<?php

namespace App\Http\Controllers;

use App\Mail\ClientLookupCodeMail;
use App\Models\Client;
use App\Models\ClientLookupCode;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Запрос истории обслуживания клиентом — публичный, по email + OTP.
 *
 * Поток:
 *   1. GET  /my-visits             → форма email
 *   2. POST /my-visits/send-code   → если клиент найден, шлём 6-значный код
 *   3. GET  /my-visits/verify      → форма ввода кода
 *   4. POST /my-visits/verify      → проверяем код, сохраняем client_id в session
 *   5. GET  /my-visits/history     → детальная история визитов
 *   6. POST /my-visits/logout      → сброс session (для запроса по другому email)
 *
 * Защита: rate-limit на отправку и ввод кода в routes/web.php.
 */
class ClientLookupController extends Controller
{
    private const CODE_TTL_MINUTES = 10;

    private const SESSION_KEY = 'client_lookup_id';

    public function showForm(): View
    {
        return view('lookup.form');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $client = Client::where('email', $email)->first();

        // Намеренно НЕ раскрываем, существует клиент или нет — иначе через форму
        // можно перебирать email'ы и узнавать клиентов сервиса (privacy leak).
        if ($client) {
            $code = (string) random_int(100000, 999999);

            ClientLookupCode::create([
                'email' => $email,
                'code' => $code,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
                'ip_address' => $request->ip(),
            ]);

            Mail::to($email)->send(new ClientLookupCodeMail($code, self::CODE_TTL_MINUTES));
        }

        return redirect()
            ->route('lookup.verify-form', ['email' => $email])
            ->with('status', 'Если такой клиент зарегистрирован, мы отправили код на указанный email.');
    }

    public function showCodeForm(Request $request): View
    {
        return view('lookup.verify', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = mb_strtolower(trim($data['email']));

        $record = ClientLookupCode::where('email', $email)
            ->where('code', $data['code'])
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (! $record || ! $record->isValid()) {
            if ($record) {
                $record->increment('attempts');
            }

            return back()
                ->withInput()
                ->withErrors(['code' => 'Неверный или просроченный код.']);
        }

        $client = Client::where('email', $email)->first();
        if (! $client) {
            return back()->withErrors(['code' => 'Клиент не найден.']);
        }

        $record->update(['used_at' => now()]);

        // Старые неиспользованные коды этого email гасим
        ClientLookupCode::where('email', $email)
            ->whereNull('used_at')
            ->where('id', '!=', $record->id)
            ->update(['used_at' => now()]);

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, $client->id);

        return redirect()->route('lookup.dashboard');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $clientId = $request->session()->get(self::SESSION_KEY);

        if (! $clientId) {
            return redirect()->route('lookup.form');
        }

        $client = Client::with([
            'cars.brand',
            'cars.model',
        ])->find($clientId);

        if (! $client) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('lookup.form');
        }

        $orders = Order::with([
            'car.brand',
            'car.model',
            'branch',
            'services',
            'parts',
            'payments',
        ])
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get();

        $totalSpent = $orders->sum(fn (Order $o) => (float) $o->paid_amount);
        $totalOrders = $orders->count();

        return view('lookup.dashboard', [
            'client' => $client,
            'orders' => $orders,
            'totalSpent' => $totalSpent,
            'totalOrders' => $totalOrders,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();

        return redirect()->route('lookup.form');
    }
}
