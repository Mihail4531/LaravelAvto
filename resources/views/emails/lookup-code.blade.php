<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения · АвтоСервис</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',Arial,sans-serif;background:#FAF7F2;">

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FAF7F2;padding:40px 16px;">
    <tr>
        <td align="center">

            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="max-width:560px;width:100%;background:#FFFFFF;border-radius:24px;overflow:hidden;box-shadow:0 8px 32px rgba(14,12,10,0.08);">

                {{-- Шапка --}}
                <tr>
                    <td style="background:#0E0C0A;padding:32px 40px;text-align:center;">
                        <div style="display:inline-flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;background:#E97A4B;border-radius:12px;display:inline-block;text-align:center;line-height:40px;">
                                <span style="color:#0E0C0A;font-weight:900;font-size:20px;">A</span>
                            </div>
                            <span style="color:#FFFFFF;font-weight:800;font-size:18px;letter-spacing:-0.02em;vertical-align:middle;">АвтоСервис</span>
                        </div>
                    </td>
                </tr>

                {{-- Тело --}}
                <tr>
                    <td style="padding:48px 40px 40px;">
                        <div style="color:#E97A4B;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;margin-bottom:16px;">
                            Подтверждение входа
                        </div>

                        <h1 style="margin:0 0 16px;font-size:28px;font-weight:800;line-height:1.15;color:#0E0C0A;letter-spacing:-0.02em;">
                            Ваш код<br>подтверждения
                        </h1>

                        <p style="margin:0 0 32px;font-size:15px;color:#5A5147;line-height:1.6;">
                            Здравствуйте! Вы запросили доступ к истории обслуживания в АвтоСервисе. Введите этот код на сайте, чтобы продолжить:
                        </p>

                        {{-- Код --}}
                        <div style="background:linear-gradient(135deg,#FFF5EE 0%,#FFE6D5 100%);border:1px solid #FFD9C2;border-radius:20px;padding:32px;text-align:center;margin-bottom:32px;">
                            <div style="color:#B34E29;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;margin-bottom:12px;">
                                Код подтверждения
                            </div>
                            <div style="font-family:'Courier New',monospace;font-size:42px;font-weight:900;color:#0E0C0A;letter-spacing:14px;line-height:1;">
                                {{ $code }}
                            </div>
                            <div style="color:#8C3C20;font-size:12px;margin-top:14px;">
                                Действителен {{ $ttlMinutes }} минут
                            </div>
                        </div>

                        <p style="margin:0;font-size:13px;color:#8A857B;line-height:1.6;">
                            Если вы не запрашивали код — просто проигнорируйте это письмо. Никто не получит доступ к вашим данным без этого кода.
                        </p>
                    </td>
                </tr>

                {{-- Футер --}}
                <tr>
                    <td style="background:#F4EFE7;padding:24px 40px;text-align:center;border-top:1px solid #E8E1D4;">
                        <div style="color:#8A857B;font-size:12px;line-height:1.6;">
                            АвтоСервис · автоматическая рассылка<br>
                            На это письмо отвечать не нужно
                        </div>
                    </td>
                </tr>

            </table>

            {{-- Подпись под карточкой --}}
            <div style="margin-top:24px;color:#B5B1A8;font-size:11px;text-align:center;">
                © {{ date('Y') }} АвтоСервис · Ремонт и обслуживание автомобилей
            </div>

        </td>
    </tr>
</table>

</body>
</html>
