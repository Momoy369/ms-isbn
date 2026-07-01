# Blueprint Refactor Book Show: ISBN Workflow dan Approval

Tanggal: 2026-07-01
Status: Draft siap implementasi
Scope: Penyederhanaan proses pada halaman Book Show agar operasional tim lebih fokus, minim langkah manual, dan konsisten terhadap state workflow.

## 1) Masalah Utama Saat Ini

1. Aksi ISBN tersebar di banyak panel sehingga operator harus bolak-balik area halaman.
2. Stepper pipeline masih mengacu ke daftar global, belum selalu sinkron dengan workflow dinamis per paket.
3. Approval editor/layout/author belum diposisikan sebagai gate tunggal yang memandu transisi state.
4. Ada banyak tombol granular generate dokumen yang valid, tetapi membebani operator harian.
5. Monitoring (audit, histori) bercampur dengan panel operasional, membuat fokus aksi kurang jelas.

## 2) Target Desain Baru (Action Center)

Book Show dipecah secara konseptual menjadi 2 zona utama:

1. Zona Operasional (Action Center)

- Menampilkan status saat ini, blocker, dan hanya aksi yang valid sekarang.
- Menyediakan 1 aksi utama (primary CTA) + aksi tambahan terbatas (secondary).

2. Zona Monitoring

- Menampilkan histori audit, file aktif, review, assignment history, activity log.
- Bersifat read-mostly, bukan tempat eksekusi langkah utama workflow.

## 3) Struktur Panel yang Diusulkan

Panel baru pada kolom kanan Book Show:

1. Workflow Snapshot

- State saat ini
- Progress
- Tanggal milestone
- Paket yang dipilih

2. Blockers & Readiness

- DP payment status
- Missing ISBN audit files
- Missing ISBN package files
- Approval matrix (editor/layout/author)
- Hasil audit terakhir (pass/fail ringkas)

3. Next Action (Primary)

- Satu tombol utama berdasarkan state
- Teks tombol kontekstual
- Alasan disabled jika belum memenuhi syarat

4. Secondary Actions

- Generate dokumen individual
- Lock metadata
- Manual sync assignment
- Khusus role tertentu

5. Perpusnas Stage

- Submit ke Perpusnas
- Verifikasi/terbit ISBN
- Ringkasan hasil validasi API

## 4) Mapping State ke Aksi (Single Source of Action)

Gunakan workflow dinamis dari model, lalu tentukan primary action per state:

1. draft

- Primary: Mulai Produksi (lanjut ke state berikutnya)
- Guard: DP paid

2. editing / editing_review / layout / layout_review / cover_design / cover_review

- Primary: Lanjutkan Tahap
- Guard: DP paid
- Secondary: upload/review terkait tahap

3. acc_penulis

- Primary (author): Setujui Naskah
- Primary (non-author): Tunggu persetujuan penulis
- Guard: hanya pemilik naskah

4. audit_isbn

- Primary: Jalankan Audit ISBN
- Guard: tidak ada hard blocker
- Jika ada missing file: tetap bisa audit, tampilkan warning terstruktur

5. ready_for_isbn

- Primary (admin/isbn/superadmin): Submit ke Perpusnas
- Primary (role lain): Menunggu submit oleh tim ISBN
- Guard: role + readiness minimum

6. isbn_submitted

- Primary (admin/isbn/superadmin): Verifikasi ISBN Terbit (API)
- Guard: input ISBN + tanggal terbit valid

7. isbn_approved

- Primary: Finalisasi Produksi
- Catatan: dapat otomatis selesai jika validasi API sukses

8. selesai

- Primary: Tidak ada (state final)
- Tampilkan badge final + tautan deliverables

## 5) Mapping Guard (Syarat Disable)

Semua guard dikumpulkan dalam satu evaluator agar view tidak berat logika:

1. dp_paid
2. author_approval_allowed
3. can_submit_isbn
4. can_approve_isbn
5. has_missing_audit_files
6. has_missing_package_files
7. is_finished
8. user_can_control_isbn

Output evaluator:

- current_state
- next_primary_action
- action_enabled
- disabled_reason
- blocker_list
- warning_list

## 6) Desain Endpoint: Keep vs Deprecate

Tetap dipakai (keep):

1. POST /books/{book}/next-workflow
2. POST /books/{book}/author-approval
3. POST /books/{book}/submit-isbn
4. POST /books/{book}/approve-isbn
5. POST /books/{book}/audit
6. POST /books/{book}/generate-package

Tetap ada, tapi dipindah ke Secondary (advanced/manual):

1. POST /books/{book}/generate/title-page
2. POST /books/{book}/generate/request-letter
3. POST /books/{book}/generate/copyright
4. POST /books/{book}/generate/attachment
5. POST /books/{book}/metadata-analyze
6. POST /books/{book}/manuscript-analyze
7. POST /books/{book}/lock-metadata
8. POST /books/{book}/sync-assignment

Deprecate bertahap dari UI utama:

1. POST /books/{book}/approve/{type}
   Alasan: Approval matrix lebih tepat digeser ke gate otomatis state-based. Endpoint tetap dipertahankan sementara untuk backward compatibility, tapi disembunyikan dari panel utama.

Endpoint baru yang disarankan:

1. GET /books/{book}/workflow-action-state

- Return JSON evaluator state+guard untuk view/API frontend.

2. POST /books/{book}/workflow/execute-primary

- Menjalankan aksi utama sesuai state saat ini.
- Menurunkan kompleksitas banyak tombol manual.

3. POST /books/{book}/workflow/prepare-isbn

- Orchestrated action: generate all dokumen ISBN + audit + ringkasan blocker.
- Tidak langsung submit Perpusnas, hanya preparation.

## 7) Service Layer yang Disarankan

Tambahkan service baru:

1. App\Services\BookWorkflowActionService

- evaluate(Book, User): array
- executePrimary(Book, User): ResultDTO
- prepareIsbn(Book, User): ResultDTO

2. App\Services\BookWorkflowGuardService

- evaluateGuards(Book, User): GuardResult
- sumber tunggal disabled reason dan blocker.

Perubahan service eksisting:

1. ApprovalService

- Fungsi approval matrix dijadikan helper evaluasi gate, bukan tombol manual utama.

2. WorkflowService

- Tetap untuk transisi status, tapi dipanggil melalui ActionService agar terpusat.

## 8) Perubahan View yang Direncanakan

1. books/partials/pipeline.blade.php

- Render step dari workflowSteps() (bukan daftar global).

2. books/partials/approvals.blade.php

- Ubah menjadi panel ringkasan approval (read-only) + status gate.
- Tombol Approve manual dipindah ke advanced area atau disembunyikan role tertentu.

3. books/partials/quick-actions.blade.php

- Transform menjadi Action Center:
    - Primary action card
    - Blockers card
    - Secondary actions (accordion)

4. books/show.blade.php

- Tegaskan pemisahan antara Operasional dan Monitoring.

## 9) Kriteria Sukses (Definition of Done)

1. Operator dapat menyelesaikan alur ISBN dari satu panel utama tanpa mencari tombol di banyak tempat.
2. Untuk setiap state, hanya ada satu primary CTA yang jelas.
3. Alasan disable selalu muncul dan mudah dipahami.
4. Stepper dan aksi konsisten dengan workflow dinamis paket.
5. Tidak ada regresi pada route lama (kompatibilitas tetap aman).
6. Aktivitas penting tetap tercatat pada log aktivitas buku.

## 10) Rencana Implementasi Bertahap

Fase 1 (UI-only simplification, low risk)

1. Refactor quick-actions menjadi Action Center.
2. Sinkronkan pipeline ke workflowSteps().
3. Jadikan approvals read-only ringkas.

Fase 2 (service consolidation)

1. Tambah GuardService + ActionService.
2. Pindahkan logika if/guard dari blade ke service.
3. Tambah endpoint workflow-action-state.

Fase 3 (orchestration)

1. Tambah execute-primary dan prepare-isbn.
2. Wire tombol primary ke endpoint baru.
3. Sisakan tombol granular di advanced section.

Fase 4 (cleanup)

1. Deprecation notice untuk approve/{type} dari UI utama.
2. Update dokumentasi teknis dan manual tim.

## 11) Risiko dan Mitigasi

1. Risiko: perubahan perilaku role-based action.
   Mitigasi: uji matrix role admin/isbn/superadmin/author/non-author.

2. Risiko: state tidak sinkron karena endpoint lama masih aktif.
   Mitigasi: semua transisi critical diarahkan via ActionService, endpoint lama menjadi wrapper.

3. Risiko: kebingungan tim saat transisi.
   Mitigasi: tambahkan helper text pada panel Action Center selama masa adopsi.
