<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Status Registrasi — {{ $appName }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 16px;
            box-sizing: border-box;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 32px 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .content {
            padding: 32px;
        }
        .badge-danger {
            display: inline-block;
            background-color: #fef2f2;
            color: #dc2626;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 9999px;
            border: 1px solid #fecaca;
            margin-bottom: 16px;
        }
        h2 {
            margin: 0 0 12px;
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
        }
        p {
            margin: 0 0 16px;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            margin: 20px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .info-table td {
            padding: 6px 0;
            vertical-align: top;
        }
        .info-table td.label {
            width: 140px;
            color: #64748b;
            font-weight: 500;
        }
        .info-table td.val {
            color: #0f172a;
            font-weight: 600;
        }
        .notice {
            background-color: #f8fafc;
            border-left: 4px solid #64748b;
            padding: 12px 14px;
            border-radius: 4px;
            font-size: 13px;
            color: #475569;
            margin-top: 20px;
            line-height: 1.5;
        }
        .footer {
            border-top: 1px solid #e2e8f0;
            padding: 20px 32px;
            text-align: center;
            background-color: #f8fafc;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ $appName }}</h1>
                <p>Enterprise Inventory & Asset Management System</p>
            </div>
            <div class="content">
                <span class="badge-danger">Pendaftaran Belum Disetujui</span>
                <h2>Halo, {{ $user->name }},</h2>
                <p>
                    Terima kasih telah mengajukan permohonan pendaftaran akun di sistem <strong>{{ $appName }}</strong>.
                </p>
                <p>
                    Setelah dilakukan peninjauan oleh Administrator, kami memberitahukan bahwa permohonan pendaftaran akun Anda saat ini <strong>belum dapat disetujui</strong>.
                </p>

                <div class="info-card">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Terdaftar</td>
                            <td class="val">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email Pendaftaran</td>
                            <td class="val">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="label">Departemen</td>
                            <td class="val">{{ $user->department?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Status</td>
                            <td class="val" style="color: #dc2626;">Ditolak (Rejected)</td>
                        </tr>
                    </table>
                </div>

                <div class="notice">
                    Jika Anda merasa terdapat kekeliruan dalam keputusan ini atau memerlukan akses resmi ke dalam sistem inventaris, silakan hubungi tim Administrator IT atau divisi Human Resources (HR) di instansi Anda.
                </div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi.
                <br>Email otomatis, mohon tidak membalas pesan ini.
            </div>
        </div>
    </div>
</body>
</html>
