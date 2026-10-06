<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Disetujui — {{ $appName }}</title>
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
        .badge-success {
            display: inline-block;
            background-color: #ecfdf5;
            color: #059669;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
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
        .role-tag {
            display: inline-block;
            background-color: #e0e7ff;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0 20px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .notice {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 14px;
            border-radius: 4px;
            font-size: 12px;
            color: #92400e;
            margin-top: 24px;
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
                <span class="badge-success">Akun Aktif & Disetujui</span>
                <h2>Halo, {{ $user->name }}!</h2>
                <p>
                    Selamat! Permohonan pendaftaran akun Anda di <strong>{{ $appName }}</strong> telah diverifikasi dan disetujui oleh Super Administrator. Akun Anda kini telah aktif dan siap digunakan.
                </p>

                <div class="info-card">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Lengkap</td>
                            <td class="val">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email Login</td>
                            <td class="val">{{ $user->email }}</td>
                        </tr>
                        @if($user->employee_id)
                        <tr>
                            <td class="label">Nomor Karyawan (NIP)</td>
                            <td class="val">{{ $user->employee_id }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Departemen</td>
                            <td class="val">{{ $user->department?->name ?? '-' }}</td>
                        </tr>
                        @if($user->position)
                        <tr>
                            <td class="label">Jabatan</td>
                            <td class="val">{{ $user->position }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Peran (Role)</td>
                            <td class="val">
                                @forelse($user->roles as $role)
                                    <span class="role-tag">{{ $role->name }}</span>
                                @empty
                                    <span class="role-tag">Requester</span>
                                @endforelse
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="btn-wrapper">
                    <a href="{{ $loginUrl }}" class="btn" target="_blank">
                        Masuk ke Sistem
                    </a>
                </div>

                <div class="notice">
                    <strong>Catatan Keamanan:</strong> Gunakan kata sandi yang telah Anda daftarkan. Jangan pernah membagikan kredensial login Anda kepada siapa pun demi keamanan data inventaris dan aset perusahaan.
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
