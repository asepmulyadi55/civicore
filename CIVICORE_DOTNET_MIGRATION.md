# CiviCore — .NET Core + Supabase (PostgreSQL) Migration Roadmap
> **Scope:** Backend API rewrite only. React SPA frontend is unchanged.
> **Target:** AWS Lightsail $5/month (512 MB RAM, 2 vCPU, 20 GB SSD)
> **Database:** Supabase (PostgreSQL + Storage)
> **Stack:** ASP.NET Core 8 (LTS) · EF Core 8 (Npgsql) · C# 12

---

## 🗺️ Project Structure Reference

```
CiviCore.Api/              ← Main ASP.NET Core Web API project
├── Controllers/
├── DTOs/
├── Middleware/
├── Services/
├── Repositories/
├── Models/                ← EF Core entity classes
├── Data/
│   ├── AppDbContext.cs
│   └── Migrations/
├── Policies/              ← Authorization policies
├── Filters/               ← Action filters (permission checks)
├── Extensions/            ← IServiceCollection extension methods
└── Program.cs

CiviCore.Core/             ← Shared interfaces & domain logic
├── Interfaces/
├── Enums/
└── Helpers/               ← Encryption, UUID, etc.

CiviCore.Tests/            ← xUnit test project
```

---

## Pre-Flight Checklist

- [ ] Install .NET 8 SDK (`dotnet --version` → confirm 8.x)
- [ ] Install EF Core CLI tools: `dotnet tool install --global dotnet-ef`
- [ ] Create a **Supabase Project** in the SAME REGION as your AWS Lightsail instance (e.g., `ap-southeast-1` Singapore)
- [ ] Create a Supabase Storage Bucket named `civicore-media` and set it to Private.
- [ ] Create GitHub repo for new backend: `civicore-api`
- [ ] Confirm React SPA base URL and CORS origin for local dev
- [ ] Export current MySQL schema dump as reference (`mysqldump --no-data`)

---

---

# PHASE 1 — Base Architecture & Database Setup

> **Goal:** A running ASP.NET Core 8 API that connects to Supabase PostgreSQL via EF Core, with all 18 core tables migrated, seeded, and queryable.

---

## 1.1 Project Initialization

- [ ] Create solution: `dotnet new sln -n CiviCore`
- [ ] Create API project: `dotnet new webapi -n CiviCore.Api --use-controllers`
- [ ] Create class library: `dotnet new classlib -n CiviCore.Core`
- [ ] Create test project: `dotnet new xunit -n CiviCore.Tests`
- [ ] Add all projects to solution (`dotnet sln add`)
- [ ] Add project references (`CiviCore.Api` → `CiviCore.Core`, `CiviCore.Tests` → `CiviCore.Api`)
- [ ] Install NuGet packages in `CiviCore.Api`:
  - [ ] `Npgsql.EntityFrameworkCore.PostgreSQL`
  - [ ] `Microsoft.EntityFrameworkCore.Design`
  - [ ] `Microsoft.AspNetCore.Identity.EntityFrameworkCore`
  - [ ] `supabase-csharp` (for Supabase Storage)
  - [ ] `AutoMapper.Extensions.Microsoft.DependencyInjection`
  - [ ] `Serilog.AspNetCore` + `Serilog.Sinks.PostgreSQL`
  - [ ] `ClosedXML` (Excel export)
  - [ ] `Swashbuckle.AspNetCore` (Swagger)

---

## 1.2 EF Core & Supabase PostgreSQL Configuration

- [ ] Create `Data/AppDbContext.cs` extending `IdentityDbContext<ApplicationUser, ApplicationRole, Guid>`
- [ ] Register Postgres in `Program.cs`:
  ```csharp
  builder.Services.AddDbContext<AppDbContext>(opt =>
      opt.UseNpgsql(builder.Configuration.GetConnectionString("SupabaseConnection")));
  ```
- [ ] Add to `appsettings.json` (Get string from Supabase Dashboard > Database > Connection String > URI):
  ```json
  "ConnectionStrings": {
    "SupabaseConnection": "Host=aws-0-ap-southeast-1.pooler.supabase.com;Port=6543;Database=postgres;Username=postgres.your_project_ref;Password=your_password;Pooling=true;"
  }
  ```

### PostgreSQL & EF Core Advantages (over SQLite)

> **UUIDs:** Postgres natively supports `uuid` columns. EF Core automatically maps C# `Guid` to `uuid`. Generate them via `Guid.NewGuid()` in C#.

> **Enums:** EF Core can map C# enums to native PostgreSQL ENUM types!
> Add `builder.HasPostgresEnum<HouseStatus>();` in `OnModelCreating`, or simply let EF Core store them as `integer` or `text` (using `.HasConversion<string>()`). Mapping as string is often safest for migrations.

> **Dates & Decimals:** Unlike SQLite, Postgres natively handles `DateOnly` (`date` column) and `decimal` (`numeric` column) perfectly without any text conversions.

> **Encrypted Columns:** `family_card_number` is encrypted at the application layer in Laravel. Implement the same in .NET using `IEncryptionService` with `AES-256`. Store as `text` or `bytea` in Postgres.

---

## 1.3 Define All EF Core Entity Models

Map all 18 Laravel tables to C# entity classes:

- [ ] `ApplicationUser.cs` (extends `IdentityUser<Guid>`) — add: `IsActive`, `GoogleId`, `BlockId`, `UnitNumber`, `Avatar`, `Language`, `SessionToken`, `LastLoginAt`, `LastActiveAt`, `TwoFactorSecretKey`, `TwoFactorEnabledAt`
- [ ] `ApplicationRole.cs` (extends `IdentityRole<Guid>`) — add: `Description`, `Style`, `Permissions` (navigation)
- [ ] `Permission.cs` — `Id (Guid)`, `RoleId (Guid)`, `PermissionKey (string)` e.g. `"payments.approve"`
- [ ] `Block.cs` — `Id`, `Name`, `Description`, `Users` (nav), `Units` (nav)
- [ ] `BlockUser.cs` — Pivot for Block ↔ User many-to-many (from migration `2026_06_06`)
- [ ] `Unit.cs` — `Id`, `BlockId`, `UnitNumber`, `HouseStatus (enum)`
- [ ] `Resident.cs` — `Id`, `UserId?`, `BlockId`, `UnitId`, `Fullname`, `Phone`, `Email?`, `IsActive`, `FamilyCardNumber (encrypted)`, `Notes?`, `PhotoPath?`, `HouseStatus`, `RentPeriodStart?`, `RentPeriodEnd?`
- [ ] `FamilyMember.cs` — `Id`, `ResidentId`, `Fullname`, `Relationship?`, `IsHead`, `PhotoPath?`
- [ ] `PaymentMethod.cs` — `Id`, `Name`, `Description?`, `IsActive`
- [ ] `PaymentRecord.cs` — `Id`, `ResidentId`, `BatchId?`, `PaymentMonth (DateOnly)`, `Amount (decimal)`, `PaymentMethodId`, `ProofPath?`, `Status (enum)`, `RejectionReason?`, `Notes?`, `SubmittedBy (Guid)`, `ApprovedBy? (Guid)`, `ApprovedAt?`, `BlockSnapshot?`, `UnitSnapshot?`
- [ ] `FeeHistory.cs` — `Id`, `ResidentId`, `Amount`, `EffectiveFrom (DateOnly)`
- [ ] `Setting.cs` — `Id (Guid)`, `Key (string)`, `Value (string?)`
- [ ] `MediaFile.cs` — `Id`, `UserId`, `ModelType`, `ModelId`, `FilePath`, `FileName`, `FileSize`, `MimeType`
- [ ] `FinanceTransaction.cs` — `Id`, `Type`, `Amount`, `Description`, `Date`, `ReportId?`
- [ ] `FinanceReport.cs` — `Id`, `Title`, `PeriodStart`, `PeriodEnd`, `Status`, `RejectedReason?`, `CreatedBy`
- [ ] `OrganizationPeriod.cs` + `OrganizationPosition.cs`
- [ ] `Meeting.cs` + `MeetingAttendance.cs` + `MeetingImage.cs`
- [ ] `PropertyListing.cs`

---

## 1.4 Configure Relationships in `OnModelCreating`

- [ ] `Block` → `Units` (one-to-many, cascade delete)
- [ ] `Block` → `Users` via `BlockUser` pivot (many-to-many)
- [ ] `Unit` → `Resident` (one-to-one, optional)
- [ ] `Resident` → `FamilyMembers` (one-to-many)
- [ ] `Resident` → `PaymentRecords` (one-to-many)
- [ ] `Resident` → `FeeHistories` (one-to-many)
- [ ] `PaymentRecord` → `PaymentMethod` (many-to-one)
- [ ] `PaymentRecord`.`SubmittedBy` → `ApplicationUser` (no cascade — use `DeleteBehavior.Restrict`)
- [ ] `PaymentRecord`.`ApprovedBy` → `ApplicationUser` (nullable, `DeleteBehavior.SetNull`)
- [ ] `MediaFile` — polymorphic via `ModelType` + `ModelId` strings
- [ ] Apply **unique index** on `Setting.Key`
- [ ] Apply **performance indexes** (port from migration `2026_03_03_151000_add_performance_indexes`)

---

## 1.5 Base Repository Pattern & Unit of Work

- [ ] Create `IRepository<T>` interface in `CiviCore.Core/Interfaces/`
- [ ] Create `Repository<T>` generic implementation
- [ ] Create `IUnitOfWork` interface
- [ ] Create `UnitOfWork` implementation wrapping `AppDbContext`
- [ ] Register both as scoped services in `Program.cs`

---

## 1.6 Run Migrations & Seed Data

- [ ] Create initial migration: `dotnet ef migrations add InitialSchema`
- [ ] Apply migration: `dotnet ef database update`
- [ ] Create `DataSeeder.cs` to seed:
  - [ ] Default roles: `admin`, `treasurer`, `block_coordinator`, `resident`
  - [ ] Default permissions per role
  - [ ] Default admin user (`ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_USERNAME` from env)
  - [ ] Default payment methods (Cash, Bank Transfer)
  - [ ] Default settings (pagination defaults, max accounts per unit = 3)
- [ ] Call seeder in `Program.cs` on app startup

---

## ✅ Phase 1 — Success Criteria

| Test | Expected Result |
|------|----------------|
| `GET /swagger` | Swagger UI loads, all endpoints visible |
| `dotnet ef database update` | Runs without errors, tables created in Supabase |
| Inspect DB in Supabase Table Editor | All 18+ tables present with correct columns |
| Seed check: query `roles` table | 4 roles seeded with correct permissions |
| Unit test: `Repository<Block>.GetAllAsync()` | Returns empty list without exception |

---

---

# PHASE 2 — Authentication & User Management

> **Goal:** Fully functional auth system: email/password login, Google OAuth, TOTP 2FA, single-session enforcement, and role/permission-based API protection.

---

## 2.1 ASP.NET Core Identity Setup

- [ ] Configure Identity in `Program.cs`:
  ```csharp
  builder.Services.AddIdentity<ApplicationUser, ApplicationRole>(opt => {
      opt.Password.RequireDigit = true;
      opt.Password.RequiredLength = 8;
      opt.Lockout.MaxFailedAccessAttempts = 5;
  })
  .AddEntityFrameworkStores<AppDbContext>()
  .AddDefaultTokenProviders();
  ```
- [ ] Configure **cookie-based session** auth (same as Laravel session driver):
  ```csharp
  builder.Services.ConfigureApplicationCookie(opt => {
      opt.LoginPath = "/api/auth/login";
      opt.AccessDeniedPath = "/api/auth/forbidden";
      opt.Cookie.HttpOnly = true;
      opt.Cookie.SameSite = SameSiteMode.Strict;
      opt.ExpireTimeSpan = TimeSpan.FromMinutes(120);
  });
  ```
- [ ] Add CORS policy allowing your React SPA origin

---

## 2.2 Auth Controller & DTOs

- [ ] Create `AuthController.cs` with endpoints:
  - [ ] `POST /api/auth/login` — email + password, returns user + role
  - [ ] `POST /api/auth/logout` — clears session cookie
  - [ ] `POST /api/auth/register` — self-registration (creates inactive user)
  - [ ] `POST /api/auth/forgot-password` — sends reset email via SMTP
  - [ ] `POST /api/auth/reset-password` — validates token, sets new password

---

## 2.3 Two-Factor Authentication (TOTP)

> Replaces `pragmarx/google2fa-laravel`

- [ ] Install NuGet: `Otp.NET` + `QRCoder`
- [ ] Add to `AuthController`:
  - [ ] `POST /api/auth/2fa/setup` — generates TOTP secret + QR code PNG
  - [ ] `POST /api/auth/2fa/verify` — verifies TOTP code, marks 2FA enabled
  - [ ] `POST /api/auth/2fa/disable` — disables 2FA (admin only)
- [ ] Add `TwoFactorChallenge` step to login flow: if 2FA enabled, return `requires_2fa: true` flag before issuing session

---

## 2.4 Google OAuth

- [ ] Install NuGet: `Microsoft.AspNetCore.Authentication.Google`
- [ ] Add to `AuthController`:
  - [ ] `GET /api/auth/google` — redirects to Google consent screen
  - [ ] `GET /auth/google/callback` — handles callback, finds or creates user by `GoogleId`

---

## 2.5 Single-Session Enforcement Middleware

> Replicates Laravel's `session_token` conflict detection

- [ ] Create `SessionConflictMiddleware.cs`:
  - On each authenticated request, compare `SessionToken` in cookie vs DB
  - If mismatch → return `401` with `{"code": "SESSION_CONFLICT"}`
- [ ] On successful login: generate new `SessionToken`, save to DB, set in cookie

---

## 2.6 Permission-Based Authorization System

- [ ] Create `PermissionRequirement.cs` + `PermissionHandler.cs`
  - Loads user's role permissions from DB (cache in memory)
  - Checks if permission matches policy
- [ ] Register all permission policies in `Program.cs`
- [ ] Create `[RequirePermission("...")]` attribute

---

## 2.7 User Management API

- [ ] Create `UserController.cs` with standard CRUD, activate/deactivate, role assignment, and block assignment endpoints.

---

---

# PHASE 3 — Master Data Management

> **Goal:** Full CRUD APIs for Blocks, Units, Residents, and Family Members with proper relational integrity and pagination.

---

## 3.1 Blocks & Units API

- [ ] Create `BlockController.cs` (CRUD + list units in block + assign coordinators)
- [ ] Create `UnitController.cs` (CRUD)
- [ ] Validate: Block Coordinator can only view units in their assigned block(s)

---

## 3.2 Residents (Householders) API

- [ ] Create `ResidentController.cs`
- [ ] Apply Block Coordinator scope: filter queries by `coordinator.blockIds`
- [ ] Apply Resident scope: only own data visible

### Encryption Service for PostgreSQL

> **Encrypted Column — `FamilyCardNumber`:**
> 1. Create `IEncryptionService` interface with `Encrypt(string)` / `Decrypt(string)`.
> 2. Implement with `AES-256-GCM` using `System.Security.Cryptography.AesGcm`.
> 3. Handle encrypt on write and decrypt on read in the service layer (not the DB layer).

---

## 3.3 Family Members & Roles API

- [ ] Create `FamilyMemberController.cs` (CRUD + mark as head of household)
- [ ] Create `RoleController.cs` (List roles, update permissions)

---

---

# PHASE 4 — Core Financial Module (CiviPay)

> **Goal:** Full payment lifecycle — submission, proof upload, multi-stage approval/rejection, immutable history, fee management, and Excel report export.

---

## 4.1 Payment Records API

- [ ] Create `PaymentController.cs` (CRUD + approve/reject endpoints)
- [ ] Create `PaymentStatus` enum: `Unpaid`, `Pending`, `Approved`, `Rejected`

### Business Logic — Payment Service

- [ ] **Immutability Rule:** `status = Approved` records cannot be deleted by non-Admin.
- [ ] **Coordinator Edit Rule:** Edits to `Pending`/`Rejected` payments reset status to `Pending`.
- [ ] **Treasurer Auto-Approve:** Payments submitted by Treasurer → auto `Approved`.
- [ ] Store `BlockSnapshot` and `UnitSnapshot` on creation.

---

## 4.2 Fee History & Finance Reports

- [ ] Create `FeeHistoryController.cs` + `FeeHistoryService`
  - `GetFeeForMonth(residentId, month)` — finds the correct historical fee using `EffectiveFrom`
- [ ] Create `FinanceReportController.cs` + `FinanceTransactionController.cs`

---

## 4.3 Excel Report Export

- [ ] Create `ReportController.cs`
- [ ] Create `PaymentStatusExporter.cs` using `ClosedXML`:
  - Fetch raw data from Supabase.
  - Apply historical fee via `FeeHistoryService.GetFeeForMonth()`.
  - Mark paid/unpaid/pending per cell.
  - Stream response as `.xlsx`.

---

---

# PHASE 5 — Supporting Modules & Supabase Storage

> **Goal:** Dashboard API, Homepage CMS, secure Supabase Storage integration, audit logs, and production hardening.

---

## 5.1 Dashboard Aggregation API

- [ ] Create `DashboardController.cs` (Role-specific stats)
- [ ] Use `IMemoryCache` to cache dashboard results for 5 minutes.

---

## 5.2 Homepage CMS API

- [ ] Create `HomepageController.cs` (public GETs, Admin POSTs)

---

## 5.3 Secure Supabase Storage Integration

> Replicates Laravel's private storage disk using Supabase S3-compatible buckets.

- [ ] Register Supabase Client in `Program.cs`:
  ```csharp
  var supabaseUrl = builder.Configuration["Supabase:Url"];
  var supabaseKey = builder.Configuration["Supabase:ServiceRoleKey"]; // Need Service Role for private bucket overrides
  var options = new Supabase.SupabaseOptions { AutoConnectRealtime = false };
  builder.Services.AddScoped<Supabase.Client>(provider => new Supabase.Client(supabaseUrl, supabaseKey, options));
  ```
- [ ] Create `IFileStorageService` + `SupabaseStorageService`:
  - **Upload:** `await _supabase.Storage.From("civicore-media").Upload(fileBytes, filename);`
  - **Download:** `await _supabase.Storage.From("civicore-media").Download(filename);`
- [ ] Create `MediaController.cs`:
  - `GET /api/media/{id}` — Looks up `MediaFile` record in Postgres, authorizes user, then uses `SupabaseStorageService` to fetch bytes and stream them to the client. This keeps the bucket fully Private.

---

## 5.4 Meetings, Organization & Property Modules

- [ ] Create remaining controllers (`MeetingController`, `OrganizationController`, `PropertyListingController`)

---

## 5.5 Audit Logging

- [ ] Configure `Serilog.Sinks.PostgreSQL` to write audit logs directly to an `audit_logs` table in Supabase.
- [ ] Create `AuditMiddleware.cs` to intercept and log actions.

---

## 5.6 Production Hardening for $5 Lightsail

- [ ] Set up **systemd service** for the .NET API process.
- [ ] Set up **Nginx reverse proxy** to Kestrel on `localhost:5000`.
- [ ] Set up **Certbot + Let's Encrypt** SSL.
- [ ] **No Local DB Backups needed!** Supabase handles the PostgreSQL backups automatically.

---

---

## 📦 Final Technology Stack Reference

| Concern | Laravel (Old) | .NET Core 8 + Supabase (New) |
|---------|--------------|------------------------------|
| Framework | Laravel 12 | ASP.NET Core 8 |
| Language | PHP 8.2 | C# 12 |
| ORM | Eloquent | Entity Framework Core 8 (Npgsql) |
| Database | MySQL | **Supabase (PostgreSQL)** |
| File Storage | Laravel Local Storage | **Supabase Storage (Private Buckets)** |
| Auth | Laravel Auth + Sanctum | ASP.NET Core Identity + Cookies |
| OAuth | Laravel Socialite | AspNetCore.Authentication.Google |
| 2FA | pragmarx/google2fa | Otp.NET + QRCoder |
| Permissions | Custom middleware | Authorization Policies + Handlers |
| Excel Export | maatwebsite/excel | ClosedXML |
| Queue | Laravel Database Queue | `IHostedService` + BackgroundService |
| Encryption | Laravel `encrypt()` | AesGcm (System.Security.Cryptography) |
| Logging | Laravel Daily log | Serilog + PostgreSQL Sink |
| API Docs | (none) | Swashbuckle (Swagger UI) |
| Frontend | React 18 + Vite | **Unchanged** ✅ |

---

## ⏱️ Estimated Timeline

| Phase | Estimated Duration |
|-------|-------------------|
| Phase 1 — Base Architecture & Supabase Connect | 3–5 days |
| Phase 2 — Auth & User Management | 1–2 weeks |
| Phase 3 — Master Data | 1 week |
| Phase 4 — CiviPay Financial Module | 2–3 weeks |
| Phase 5 — Supporting Modules & Supabase Storage | 1–2 weeks |
| **Total** | **~6–9 weeks** |

---

*Updated for Supabase Architecture: June 2026*
