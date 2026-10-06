<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Baru Menunggu Persetujuan — {{ $appName }}</title>
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
        .badge-pending {
            display: inline-block;
            background-color: #fffbeb;
            color: #d97706;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 9999px;
            border: 1px solid #fde68a;
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
        .btn-container {
            text-align: center;
            margin: 28px 0 12px;
        }
        .btn-action {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.5;
        }
        .footer p {
            margin: 0;
            font-size: 12px;
            color: #94a3b8;
        }
        .security-note {
            background-color: #f1f5f9;
            border-left: 3px solid #0284c7;
            padding: 10px 14px;
            font-size: 12px;
            color: #475569;
            border-radius: 0 6px 6px 0;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>{{ $appName }}</h1>
                <p>Sistem Manajemen Aset & Inventaris</p>
            </div>

            <!-- Content -->
            <div class="content">
                <span class="badge-pending">⏳ Menunggu Persetujuan</span>

                <h2>Halo, {{ $admin->name }}</h2>

                <p>
                    Terdapat permohonan pendaftaran akun pengguna baru di <strong>{{ $appName }}</strong> yang membutuhkan tinjauan dan persetujuan dari Super Admin.
                </p>

                <!-- Detail Calon Pengguna -->
                <div class="info-card">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Lengkap</td>
                            <td class="val">{{ $newUser->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email Korporat</td>
                            <td class="val">{{ $newUser->email }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nomor Karyawan (NIP)</td>
                            <td class="val">{{ $newUser->employee_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Departemen</td>
                            <td class="val">{{ $newUser->department?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jabatan</td>
                            <td class="val">{{ $newUser->position ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nomor Telepon</td>
                            <td class="val">{{ $newUser->phone_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Pendaftaran</td>
                            <td class="val">{{ $newUser->created_at ? $newUser->created_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="btn-container">
                    <a href="{{ $reviewUrl }}" class="btn-action">
                        Tinjau & Proses Pendaftaran
                    </a>
                </div>

                <div class="security-note">
                    <strong>Catatan Keamanan:</strong> Pengguna tidak dapat masuk ke sistem sampai akun disetujui dan diberikan role akses yang sesuai oleh Super Admin.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Email ini dikirim secara otomatis oleh sistem {{ $appName }}.</p>
                <p>&copy; {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>
