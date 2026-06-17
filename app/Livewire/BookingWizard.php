<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Service;
use App\Models\TimeSlot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookingWizard extends Component
{
    public int $step = 1;

    // Направление перехода между шагами — для анимации (вперёд/назад)
    public string $stepDirection = 'forward';

    // Step 1: Services
    #[Url(as: 'category')]
    public ?int $categoryId = null;

    public array $serviceIds = [];

    // Step 2: Branch & Slot
    #[Url]
    public ?int $branchId = null;

    public ?int $slotId = null;

    // Step 3: Contact — ФИО собираем по частям, в client_name склеиваем через trim
    public string $clientLastName = '';

    public string $clientFirstName = '';

    public string $clientMiddleName = '';

    public string $clientPhone = '';

    public string $clientEmail = '';

    public ?int $carBrandId = null;

    public ?int $carModelId = null;

    public string $problemDescription = '';

    public bool $submitted = false;

    public ?int $appointmentId = null;

    /**
     * Предвыбор услуги по ?service=ID (переход из каталога на главной).
     */
    public function mount(): void
    {
        $serviceId = (int) request('service');

        if ($serviceId > 0 && Service::where('id', $serviceId)->where('active', true)->exists()) {
            $this->serviceIds = [$serviceId];

            // Сразу открываем нужную категорию в каталоге
            $this->categoryId = Service::whereKey($serviceId)->value('category_id');
        }

        // Если филиал всего один — выбираем его сразу: клиент не выбирает «куда»,
        // а только удобное время. Выбор появится сам, когда филиалов станет больше.
        if (! $this->branchId && $this->branches->count() === 1) {
            $this->branchId = $this->branches->first()->id;
        }
    }

    /**
     * Нужно ли показывать выбор филиала (есть из чего выбирать).
     */
    #[Computed]
    public function hasBranchChoice(): bool
    {
        return $this->branches->count() > 1;
    }

    // ── Computed ──────────────────────────────────────────────────────────

    #[Computed]
    public function rootCategories(): Collection
    {
        return Category::where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Все категории с услугами — грузятся разом, чтобы фильтрация
     * по категориям и выбор услуг работали на клиенте без round-trip.
     */
    #[Computed]
    public function serviceCatalog(): Collection
    {
        return Category::where('active', true)
            ->orderBy('sort_order')
            ->with(['services' => fn ($q) => $q->where('active', true)->orderBy('sort_order')])
            ->get();
    }

    #[Computed]
    public function services(): Collection
    {
        if (! $this->categoryId) {
            return collect();
        }

        return Service::where('category_id', $this->categoryId)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function branches(): Collection
    {
        return Branch::where('active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function timeSlots(): Collection
    {
        if (! $this->branchId) {
            return collect();
        }

        return TimeSlot::where('branch_id', $this->branchId)
            ->bookable()
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (TimeSlot $slot) => $slot->starts_at->format('Y-m-d'));
    }

    #[Computed]
    public function selectedServices(): Collection
    {
        if (empty($this->serviceIds)) {
            return collect();
        }

        return Service::whereIn('id', $this->serviceIds)->get();
    }

    #[Computed]
    public function selectedSlot(): ?TimeSlot
    {
        if (! $this->slotId) {
            return null;
        }

        return TimeSlot::find($this->slotId);
    }

    #[Computed]
    public function selectedBranch(): ?Branch
    {
        if (! $this->branchId) {
            return null;
        }

        return Branch::find($this->branchId);
    }

    #[Computed]
    public function carBrands(): Collection
    {
        return CarBrand::where('active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function carModels(): Collection
    {
        if (! $this->carBrandId) {
            return collect();
        }

        return CarModel::where('car_brand_id', $this->carBrandId)->orderBy('name')->get();
    }

    /**
     * ФИО одной строкой — для итоговой сводки на сайте и сохранения в заявке.
     */
    #[Computed]
    public function clientFullName(): string
    {
        return Appointment::composeName($this->clientLastName, $this->clientFirstName, $this->clientMiddleName);
    }

    // ── Actions ───────────────────────────────────────────────────────────

    public function selectCategory(int $id): void
    {
        $this->categoryId = $id;
        unset($this->services);
    }

    public function toggleService(int $id): void
    {
        if (in_array($id, $this->serviceIds)) {
            $this->serviceIds = array_values(array_filter($this->serviceIds, fn ($s) => $s !== $id));
        } else {
            $this->serviceIds[] = $id;
        }
        unset($this->selectedServices);
    }

    public function selectBranch(int $id): void
    {
        $this->branchId = $id;
        $this->slotId = null;
        unset($this->timeSlots, $this->selectedBranch);
    }

    public function selectSlot(int $id): void
    {
        $this->slotId = $id;
        unset($this->selectedSlot);
    }

    public function updatedCarBrandId(): void
    {
        $this->carModelId = null;
        unset($this->carModels);
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->stepDirection = 'forward';
        $this->step++;
        $this->resetErrorBag();
    }

    public function prevStep(): void
    {
        $this->stepDirection = 'back';
        $this->step = max(1, $this->step - 1);
        $this->resetErrorBag();
    }

    public function submit(): void
    {
        $this->validateCurrentStep();

        DB::transaction(function () {
            $slot = TimeSlot::lockForUpdate()->findOrFail($this->slotId);

            if (! $slot->available) {
                $this->addError('slotId', 'Выбранное время уже занято. Выберите другое.');
                $this->step = 2;

                return;
            }

            $appointment = Appointment::create([
                'branch_id' => $this->branchId,
                'time_slot_id' => $this->slotId,
                'car_brand_id' => $this->carBrandId ?: null,
                'car_model_id' => $this->carModelId ?: null,
                'client_name' => Appointment::composeName($this->clientLastName, $this->clientFirstName, $this->clientMiddleName),
                'client_phone' => $this->clientPhone,
                'client_email' => mb_strtolower(trim($this->clientEmail)),
                'problem_description' => $this->problemDescription ?: null,
                'status' => Appointment::STATUS_NEW,
            ]);

            $appointment->services()->attach($this->serviceIds);
            $slot->update(['available' => false]);

            $this->appointmentId = $appointment->id;
        });

        if (! $this->getErrorBag()->any()) {
            $this->submitted = true;
        }
    }

    private function validateCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->validate(
                ['serviceIds' => 'required|array|min:1'],
                ['serviceIds.required' => 'Выберите хотя бы одну услугу', 'serviceIds.min' => 'Выберите хотя бы одну услугу']
            ),
            2 => $this->validate(
                ['branchId' => 'required', 'slotId' => 'required'],
                ['branchId.required' => 'Выберите филиал', 'slotId.required' => 'Выберите дату и время']
            ),
            3 => $this->validate(
                [
                    'clientLastName' => 'required|min:2',
                    'clientFirstName' => 'required|min:2',
                    'clientMiddleName' => 'nullable|min:2',
                    'clientPhone' => ['required', function ($attr, $value, $fail) {
                        if (preg_match_all('/\d/', (string) $value) < 11) {
                            $fail('Введите корректный номер телефона');
                        }
                    }],
                    'clientEmail' => 'required|email:rfc|max:255',
                ],
                [
                    'clientLastName.required' => 'Введите фамилию',
                    'clientLastName.min' => 'Минимум 2 символа',
                    'clientFirstName.required' => 'Введите имя',
                    'clientFirstName.min' => 'Минимум 2 символа',
                    'clientMiddleName.min' => 'Минимум 2 символа',
                    'clientPhone.required' => 'Введите номер телефона',
                    'clientEmail.required' => 'Введите email — он нужен для просмотра истории обслуживания',
                    'clientEmail.email' => 'Введите корректный email',
                ]
            ),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.booking-wizard');
    }
}
