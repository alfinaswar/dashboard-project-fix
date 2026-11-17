# Implementation Plan: Fase 7

## Scope
- Dashboard ringkasan keuangan, langganan, dan proyek.
- Laporan dasar dengan ekspor CSV tanpa dependency baru.
- Notifikasi langganan harian dengan penanda anti-duplikat.

## Tasks
- [x] Dashboard route, query, view, and test.
- [x] Reports route, filters, CSV export, and test.
- [x] Subscription reminder command, scheduler, and test.
- [x] Full verification and diff review.

## Acceptance
- [x] Authenticated user sees Indonesian dashboard cards and active projects.
- [x] Reports export valid CSV for selected period.
- [x] Reminder command sends each subscription trigger once.
- [x] Full test suite, migration, lint, routes, and diff checks pass.

## Risks
- Existing schema may not expose every planned report relation.
- Notification mail configuration may be absent; database notification remains usable.
