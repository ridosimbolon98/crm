# Flow Proses dan Flowchart Sistem CRM Complaint

Tanggal update: 2 Maret 2026

## 1. Flow Proses End-to-End
1. Customer mengisi form complaint publik (`/` atau `/complaint-form`) tanpa login.
2. Sistem validasi data, membuat/mencocokkan data customer, lalu membuat ticket complaint.
3. Sistem kirim notifikasi ke tim internal (Admin/Manager/QA/CS).
4. Tim internal login, membuka daftar complaint, lalu memproses detail ticket.
5. Tim menambahkan catatan investigasi dan upload bukti jika diperlukan.
6. QA menyiapkan CAPA dan submit ke Manager.
7. Manager review CAPA:
8. Jika ditolak: QA revisi CAPA lalu submit ulang.
9. Jika disetujui: ticket bisa ditutup (Close) oleh QA/Manager/Admin.
10. Semua aksi terekam di audit log.
11. Data dapat diekspor ke Excel/PDF untuk pelaporan.

## 2. Flowchart Utama (Mermaid)
```mermaid
flowchart TD
    A[Customer Isi Form Publik] --> B{Validasi Form}
    B -- Tidak Valid --> A
    B -- Valid --> C[Buat/Cocokkan Master Customer]
    C --> D[Buat Ticket Complaint]
    D --> E[Notifikasi ke Tim Internal]
    E --> F[Tim Internal Review Ticket]
    F --> G[Investigasi + Catatan + Lampiran]
    G --> H[QA Submit CAPA]
    H --> I{Manager Approve?}
    I -- Reject --> J[QA Revisi CAPA]
    J --> H
    I -- Approve --> K[Close Ticket]
    K --> L[Audit Log + Reporting Export]
```

## 3. Flowchart CAPA Detail
```mermaid
flowchart TD
    A[Status Ticket: Investigating/Action Plan] --> B[QA Isi Root Cause]
    B --> C[QA Isi Corrective Action]
    C --> D[QA Isi Preventive Action]
    D --> E[QA Submit CAPA]
    E --> F{Manager Review}
    F -- Approve --> G[CAPA Approved]
    F -- Reject --> H[CAPA Rejected + Alasan]
    H --> B
    G --> I[Isi Resolution Summary]
    I --> J[Close Ticket]
```

## 4. Flow Role Akses
```mermaid
flowchart LR
    CUS[Customer Guest] -->|Input Complaint| PUB[Public Form]
    ADM[Admin] --> ALL[Semua Modul]
    QA[QA] --> OPS[Operasional Complaint + Submit CAPA]
    MGR[Manager] --> APV[Approve/Reject CAPA + Close]
    CS[CS] --> CSF[Input Complaint Internal + Catatan + Lampiran]
    VIEW[Viewer] --> REP[Dashboard + Export + Panduan]
```

## 5. Master Data Governance
1. Master Data (`Brand`, `Category`, `Severity`, `Customer`) hanya dapat dikelola oleh `Admin`.
2. Input complaint internal menggunakan referensi master data.
3. Pilihan customer pada form internal bersifat searchable agar cepat untuk data besar.

## 6. Output dan Kontrol
1. Output operasional: status ticket, timeline, CAPA status.
2. Output manajerial: KPI dashboard, export Excel/PDF.
3. Kontrol kepatuhan: audit log seluruh aksi penting.
