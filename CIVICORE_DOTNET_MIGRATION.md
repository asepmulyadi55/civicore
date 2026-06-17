# CiviCore — .NET Core + SQLite Migration Roadmap
> **Scope:** Backend API rewrite only. React SPA frontend is unchanged.
> **Target:** AWS Lightsail $5/month (512 MB RAM, 2 vCPU, 20 GB SSD)
> **Stack:** ASP.NET Core 8 (LTS) · EF Core 8 · SQLite · C# 12

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
- [ ] Install SQLite browser (DB Browser for SQLite) for inspection
- [ ] Create GitHub repo for new backend: `civicore-api`
- [ ] Confirm React SPA base URL and CORS origin for local dev
- [ ] Export current MySQL schema dump as reference (`mysqldump --no-data`)

---

---

# PHASE 1 — Base Architecture & Database Setup

> **Goal:** A running ASP.NET Core 8 API that connects to SQLite via EF Core, with all 18 core tables migrated, seeded, and queryable.

---

## 1.1 Project Initialization

- [ ] Create solution: `dotnet new sln -n CiviCore`
- [ ] Create API project: `dotnet new webapi -n CiviCore.Api --use-controllers`
- [ ] Create class library: `dotnet new classlib -n CiviCore.Core`
- [ ] Create test project: `dotnet new xunit -n CiviCore.Tests`
- [ ] Add all projects to solution (`dotnet sln add`)
- [ ] Add project references (`CiviCore.Api` → `CiviCore.Core`, `CiviCore.Tests` → `CiviCore.Api`)
- [ ] Install NuGet packages in `CiviCore.Api`:
  - [ ] `Microsoft.EntityFrameworkCore.Sqlite`
  - [ ] `Microsoft.EntityFrameworkCore.Design`
  - [ ] `Microsoft.AspNetCore.Identity.EntityFrameworkCore`
  - [ ] `AutoMapper.Extensions.Microsoft.DependencyInjection`
  - [ ] `Serilog.AspNetCore` + `Serilog.Sinks.File`
  - [ ] `ClosedXML` (Excel export, replaces maatwebsite/excel)
  - [ ] `Swashbuckle.AspNetCore` (Swagger)

---

## 1.2 EF Core & SQLite Configuration

- [ ] Create `Data/AppDbContext.cs` extending `IdentityDbContext<ApplicationUser, ApplicationRole, Guid>`
- [ ] Register SQLite in `Program.cs`:
  ```csharp
  builder.Services.AddDbContext<AppDbContext>(opt =>
      opt.UseSqlite(builder.Configuration.GetConnectionString("DefaultConnection")));
  ```
- [ ] Add to `appsettings.json`:
  ```json
  "ConnectionStrings": {
    "DefaultConnection": "Data Source=civicore.db"
  }
  ```

### SQLite & EF Core Adjustments

> **UUIDs:** SQLite stores GUIDs as `TEXT(36)`. EF Core handles this automatically when your entity key type is `Guid`. Explicitly configure in `OnModelCreating`:
> ```csharp
> modelBuilder.Entity<Block>()
>     .Property(b => b.Id)
>     .HasColumnType("TEXT")
>     .HasDefaultValueSql("(lower(hex(randomblob(4))) || '-' || ...)");
> ```
> **Simpler approach:** Let EF Core generate GUIDs in C# (`Guid.NewGuid()`) rather than relying on SQLite functions.

> **Enums:** Laravel used MySQL `ENUM` columns. Map all to `string` in SQLite with a `HasConversion<string>()` or use C# enums with a value converter:
> ```csharp
> .Property(u => u.HouseStatus)
> .HasConversion<string>();
> ```
> Enum values to port: `house_status` (owner_occupied, rented, vacant, public_facility, developer), `payment status` (unpaid, pending, approved, rejected).

> **Encrypted Columns:** `family_card_number` (on `residents`/`householders` table) was encrypted at the application layer in Laravel. Implement the same in .NET using `IEncryptionService` with `AES-256` (via `System.Security.Cryptography`). Store as `TEXT` in SQLite.

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
- [ ] `MediaFile` — polymorphic via `ModelType` + `ModelId` strings (SQLite has no JSON type; store as TEXT)
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
  - [ ] Default permissions per role (port from migration `2026_03_01_165426`)
  - [ ] Default admin user (`ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_USERNAME` from env)
  - [ ] Default payment methods (Cash, Bank Transfer)
  - [ ] Default settings (pagination defaults, max accounts per unit = 3)
- [ ] Call seeder in `Program.cs` on app startup

---

## ✅ Phase 1 — Success Criteria

| Test | Expected Result |
|------|----------------|
| `GET /swagger` | Swagger UI loads, all endpoints visible |
| `dotnet ef database update` | Runs without errors, `civicore.db` created |
| Inspect DB in SQLite browser | All 18+ tables present with correct columns |
| Seed check: query `roles` table | 4 roles seeded with correct permissions |
| Seed check: query `users` table | Admin user present with hashed password |
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
- [ ] Create DTOs: `LoginRequest`, `LoginResponse`, `RegisterRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`
- [ ] Create `IAuthService` + `AuthService` in `Services/`

---

## 2.3 Two-Factor Authentication (TOTP)

> Replaces `pragmarx/google2fa-laravel`

- [ ] Install NuGet: `Otp.NET` + `QRCoder`
- [ ] Add to `AuthController`:
  - [ ] `POST /api/auth/2fa/setup` — generates TOTP secret + QR code PNG
  - [ ] `POST /api/auth/2fa/verify` — verifies TOTP code, marks 2FA enabled
  - [ ] `POST /api/auth/2fa/disable` — disables 2FA (admin only)
- [ ] Store `TwoFactorSecretKey` (encrypted) on `ApplicationUser`
- [ ] Add `TwoFactorChallenge` step to login flow: if 2FA enabled, return `requires_2fa: true` flag before issuing session

---

## 2.4 Google OAuth

> Replaces `laravel/socialite`

- [ ] Install NuGet: `Microsoft.AspNetCore.Authentication.Google`
- [ ] Configure in `Program.cs`:
  ```csharp
  builder.Services.AddAuthentication()
      .AddGoogle(opt => {
          opt.ClientId = config["Google:ClientId"];
          opt.ClientSecret = config["Google:ClientSecret"];
          opt.CallbackPath = "/auth/google/callback";
      });
  ```
- [ ] Add to `AuthController`:
  - [ ] `GET /api/auth/google` — redirects to Google consent screen
  - [ ] `GET /auth/google/callback` — handles callback, finds or creates user by `GoogleId`
- [ ] Store `GoogleId` on `ApplicationUser`
- [ ] Auto-activate users created via Google OAuth

---

## 2.5 Single-Session Enforcement Middleware

> Replicates Laravel's `session_token` conflict detection

- [ ] Create `SessionConflictMiddleware.cs`:
  - On each authenticated request, compare `SessionToken` in cookie vs `ApplicationUser.SessionToken` in DB
  - If mismatch → return `401` with `{"code": "SESSION_CONFLICT"}`
- [ ] On successful login: generate new `SessionToken` (`Guid.NewGuid().ToString()`), save to DB, set in cookie
- [ ] Create `GET /api/auth/session-conflict` endpoint for the React SPA to redirect to
- [ ] Register middleware in `Program.cs` after auth middleware

---

## 2.6 Permission-Based Authorization System

> Replicates Laravel's `permission:` middleware

- [ ] Create `PermissionRequirement.cs` (implements `IAuthorizationRequirement`)
- [ ] Create `PermissionHandler.cs` (implements `AuthorizationHandler<PermissionRequirement>`)
  - Loads user's role permissions from DB (cache in memory for session)
  - Checks if the permission key matches
- [ ] Register all permission policies in `Program.cs`:
  ```csharp
  services.AddAuthorization(opt => {
      opt.AddPolicy("payments.approve", p => p.Requirements.Add(new PermissionRequirement("payments.approve")));
      // ... repeat for all 20+ permissions
  });
  ```
- [ ] Create `[RequirePermission("payments.approve")]` action filter attribute as a shortcut
- [ ] Create `ICacheService` to cache user permissions per session (avoids DB hit on every request)

---

## 2.7 User Management API

- [ ] Create `UserController.cs` with endpoints:
  - [ ] `GET /api/users` — list all users (paginated)
  - [ ] `GET /api/users/{id}` — get user detail
  - [ ] `POST /api/users` — admin creates user (active by default)
  - [ ] `PUT /api/users/{id}` — update user info
  - [ ] `PATCH /api/users/{id}/activate` — activate user
  - [ ] `PATCH /api/users/{id}/deactivate` — deactivate user
  - [ ] `POST /api/users/{id}/reset-password` — admin resets password
  - [ ] `POST /api/users/{id}/assign-role` — assign role to user
  - [ ] `POST /api/users/{id}/assign-block` — assign block(s) to coordinator
- [ ] Create DTOs: `UserDto`, `CreateUserRequest`, `UpdateUserRequest`

### SQLite & EF Core Adjustments

> **Max accounts per unit:** The setting `max_accounts_per_unit` is stored in the `settings` table. Check this on `POST /api/users` via `ISettingService.GetAsync("max_accounts_per_unit")`.

---

## ✅ Phase 2 — Success Criteria

| Test | Expected Result |
|------|----------------|
| `POST /api/auth/login` (valid) | Returns 200, session cookie set |
| `POST /api/auth/login` (wrong password) | Returns 401 |
| `POST /api/auth/login` (inactive user) | Returns 403 with `"user_inactive"` message |
| `POST /api/auth/login` (2FA enabled) | Returns `requires_2fa: true` |
| `GET /api/users` without auth | Returns 401 |
| `GET /api/users` with resident role | Returns 403 |
| `GET /api/users` with admin role | Returns 200 with paginated list |
| Login from second browser tab | First session invalidated (`SESSION_CONFLICT`) |
| `GET /auth/google/callback` | Redirects to dashboard or creates user |

---

---

# PHASE 3 — Master Data Management

> **Goal:** Full CRUD APIs for Blocks, Units, Residents, and Family Members with proper relational integrity and pagination.

---

## 3.1 Blocks API

- [ ] Create `BlockController.cs`:
  - [ ] `GET /api/blocks` — list all blocks (with coordinator info)
  - [ ] `GET /api/blocks/{id}` — block detail with unit count
  - [ ] `POST /api/blocks` — create block
  - [ ] `PUT /api/blocks/{id}` — update block
  - [ ] `DELETE /api/blocks/{id}` — delete block (only if no residents)
  - [ ] `GET /api/blocks/{id}/units` — list units in block
  - [ ] `POST /api/blocks/{id}/coordinators` — assign coordinators
- [ ] Create DTOs: `BlockDto`, `BlockDetailDto`, `CreateBlockRequest`
- [ ] Apply permission: `[RequirePermission("blocks.view")]`, `[RequirePermission("blocks.create")]` etc.

---

## 3.2 Units API

- [ ] Create `UnitController.cs`:
  - [ ] `GET /api/units` — list units (filterable by block)
  - [ ] `GET /api/units/{id}` — unit detail with current resident
  - [ ] `POST /api/blocks/{blockId}/units` — create unit in block
  - [ ] `PUT /api/units/{id}` — update unit (status, number)
  - [ ] `DELETE /api/units/{id}` — delete unit (only if vacant)
- [ ] Create DTOs: `UnitDto`, `CreateUnitRequest`
- [ ] Validate: Block Coordinator can only view units in their assigned block(s)

### SQLite & EF Core Adjustments

> **HouseStatus enum:** Values are `owner_occupied`, `rented`, `vacant`, `public_facility`, `developer`. Store as `TEXT` in SQLite. Apply `HasConversion<string>()` on the EF Core model. Guard against invalid values in the DTO validation layer.

---

## 3.3 Residents (Householders) API

- [ ] Create `ResidentController.cs`:
  - [ ] `GET /api/residents` — list residents (paginated, filterable by block)
  - [ ] `GET /api/residents/{id}` — resident detail
  - [ ] `POST /api/residents` — create resident
  - [ ] `PUT /api/residents/{id}` — update resident
  - [ ] `PATCH /api/residents/{id}/deactivate` — soft-deactivate
  - [ ] `PATCH /api/residents/{id}/reactivate` — reactivate
  - [ ] `GET /api/residents/{id}/payments` — payment history for resident
  - [ ] `GET /api/residents/{id}/overview` — summary for resident's own page (resident role)
- [ ] Apply Block Coordinator scope: filter queries by `coordinator.blockIds`
- [ ] Apply Resident scope: only own data visible

### SQLite & EF Core Adjustments

> **Encrypted Column — `FamilyCardNumber`:**
> 1. Create `IEncryptionService` interface with `Encrypt(string)` / `Decrypt(string)` methods.
> 2. Implement with `AES-256-GCM` using `System.Security.Cryptography.AesGcm`.
> 3. Store encryption key in `appsettings.json` under `Encryption:Key` (32-byte base64).
> 4. **Do NOT use EF Core value converters for this** — handle encrypt on write and decrypt on read in the service layer, not the DB layer, for testability.
>
> **Rent period fields:** `RentPeriodStart` and `RentPeriodEnd` map to `DATE` → use `DateOnly` in C#, stored as `TEXT` in SQLite. Configure with:
> ```csharp
> .Property(r => r.RentPeriodStart).HasColumnType("TEXT");
> ```

---

## 3.4 Family Members API

- [ ] Create `FamilyMemberController.cs`:
  - [ ] `GET /api/residents/{residentId}/family` — list family members
  - [ ] `POST /api/residents/{residentId}/family` — add family member
  - [ ] `PUT /api/residents/{residentId}/family/{memberId}` — update member
  - [ ] `DELETE /api/residents/{residentId}/family/{memberId}` — remove member
  - [ ] `PATCH /api/residents/{residentId}/family/{memberId}/set-head` — mark as head of household
- [ ] Enforce: only one `IsHead = true` per resident (enforce in service, not DB constraint)
- [ ] Create DTOs: `FamilyMemberDto`, `CreateFamilyMemberRequest`

---

## 3.5 Roles & Permissions API

- [ ] Create `RoleController.cs`:
  - [ ] `GET /api/roles` — list all roles with their permissions
  - [ ] `GET /api/roles/{id}` — role detail
  - [ ] `PUT /api/roles/{id}/permissions` — update permission set for a role
- [ ] Create DTOs: `RoleDto`, `UpdateRolePermissionsRequest`

---

## ✅ Phase 3 — Success Criteria

| Test | Expected Result |
|------|----------------|
| `GET /api/blocks` as Admin | Returns all blocks with coordinator info |
| `GET /api/blocks` as Block Coordinator | Returns only their assigned blocks |
| `GET /api/residents` as Coordinator | Returns only residents in their block |
| `GET /api/residents/{id}` as Resident (other's ID) | Returns 403 |
| `POST /api/residents` with `family_card_number` | Stored encrypted in DB |
| `GET /api/residents/{id}` | `family_card_number` returned decrypted in response |
| `POST /api/blocks/{id}/units` (vacant → occupied) | Unit status updates correctly |
| `PATCH /api/residents/{id}/family/{memberId}/set-head` | Previous head automatically unset |

---

---

# PHASE 4 — Core Financial Module (CiviPay)

> **Goal:** Full payment lifecycle — submission, proof upload, multi-stage approval/rejection, immutable history, fee management, and Excel report export.

---

## 4.1 Payment Records API

- [ ] Create `PaymentController.cs`:
  - [ ] `GET /api/payments` — list all payments (paginated, filterable by status, month, block, resident)
  - [ ] `GET /api/payments/{id}` — payment detail
  - [ ] `POST /api/payments` — submit payment (Coordinator or Treasurer)
  - [ ] `PUT /api/payments/{id}` — edit payment (Coordinator = re-queues for approval, Admin = no re-approval)
  - [ ] `DELETE /api/payments/{id}` — soft-delete (Admin only, pending/rejected only)
  - [ ] `PATCH /api/payments/{id}/approve` — approve payment (Treasurer/Admin)
  - [ ] `PATCH /api/payments/{id}/reject` — reject with reason (Treasurer/Admin)
  - [ ] `GET /api/payments/{id}/proof` — serve proof file (authenticated, private)
  - [ ] `POST /api/payments/{id}/proof` — upload proof file
- [ ] Create DTOs: `PaymentRecordDto`, `CreatePaymentRequest`, `ApprovePaymentRequest`, `RejectPaymentRequest`
- [ ] Create `PaymentStatus` enum: `Unpaid`, `Pending`, `Approved`, `Rejected`
- [ ] Implement **batch payment**: one submission covering multiple months

### Business Logic — Payment Service

- [ ] Create `IPaymentService` + `PaymentService`
- [ ] **Immutability Rule:** `status = Approved` records cannot be deleted by non-Admin. Enforce in service.
- [ ] **Coordinator Edit Rule:** If Coordinator edits a `Pending` or `Rejected` payment, status resets to `Pending`.
- [ ] **Treasurer Auto-Approve:** Payments submitted by Treasurer role → status = `Approved` immediately, `ApprovedBy = submitterId`, `ApprovedAt = UtcNow`.
- [ ] **Admin Direct Edit:** Admin can edit `Approved` payments without status change.
- [ ] Store `BlockSnapshot` and `UnitSnapshot` on creation (from migration `2026_06_15_000002`) — preserves historical block/unit even if resident is later reassigned.

### SQLite & EF Core Adjustments

> **`PaymentMonth` as `DateOnly`:** SQLite has no native date type. Store as `TEXT` in `YYYY-MM-DD` format. Configure:
> ```csharp
> .Property(p => p.PaymentMonth)
> .HasColumnType("TEXT")
> .HasConversion(
>     d => d.ToString("yyyy-MM-dd"),
>     s => DateOnly.Parse(s));
> ```
>
> **`Amount` as `decimal`:** SQLite stores as `REAL` (float). Force `TEXT` or `NUMERIC` and use EF Core's `HasColumnType("TEXT")` to preserve precision for financial data:
> ```csharp
> .Property(p => p.Amount).HasColumnType("TEXT")
> .HasConversion(d => d.ToString(), s => decimal.Parse(s));
> ```

---

## 4.2 Payment Methods API

- [ ] Create `PaymentMethodController.cs`:
  - [ ] `GET /api/payment-methods` — list active methods
  - [ ] `POST /api/payment-methods` — create (Admin only)
  - [ ] `PUT /api/payment-methods/{id}` — update
  - [ ] `PATCH /api/payment-methods/{id}/toggle` — activate/deactivate

---

## 4.3 Fee History API

- [ ] Create `FeeHistoryController.cs` (or nest under `ResidentController`):
  - [ ] `GET /api/residents/{id}/fees` — list all fee tiers for resident
  - [ ] `POST /api/residents/{id}/fees` — set new fee amount with `EffectiveFrom` date
- [ ] Implement `FeeHistoryService`:
  - `GetFeeForMonth(residentId, month)` — finds the correct fee for any given historical month using `EffectiveFrom`
  - Used by reports to ensure historically accurate amounts even after fee changes

---

## 4.4 Finance Reports & Transactions

- [ ] Create `FinanceReportController.cs`:
  - [ ] `GET /api/finance/reports` — list reports
  - [ ] `POST /api/finance/reports` — create report (Admin/Treasurer)
  - [ ] `PATCH /api/finance/reports/{id}/approve` — approve report
  - [ ] `PATCH /api/finance/reports/{id}/reject` — reject with reason
- [ ] Create `FinanceTransactionController.cs`:
  - [ ] `GET /api/finance/transactions` — list transactions (filterable by report)
  - [ ] `POST /api/finance/transactions` — add transaction to a report

---

## 4.5 Excel Report Export

> Replaces `maatwebsite/excel` using `ClosedXML`

- [ ] Create `ReportController.cs`:
  - [ ] `GET /api/reports/payment-status` — JSON version of payment status report
  - [ ] `GET /api/reports/payment-status/export` — returns `.xlsx` file
  - [ ] `GET /api/reports/block-summary` — per-block payment summary
  - [ ] `GET /api/reports/block-summary/export` — Excel export
- [ ] Create `PaymentStatusExporter.cs` using ClosedXML:
  - Months as columns, residents as rows
  - Apply historical fee via `FeeHistoryService.GetFeeForMonth()`
  - Mark paid/unpaid/pending per cell
  - Stream response: `return File(stream, "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "report.xlsx")`
- [ ] Implement query filters: `?year=2026&blockId=xxx`

---

## ✅ Phase 4 — Success Criteria

| Test | Expected Result |
|------|----------------|
| `POST /api/payments` as Coordinator | Status = `Pending`, awaiting approval |
| `POST /api/payments` as Treasurer | Status = `Approved` (auto-approved) |
| `PATCH /api/payments/{id}/approve` as Resident | Returns 403 |
| `PATCH /api/payments/{id}/approve` as Treasurer | Payment status → `Approved` |
| `PATCH /api/payments/{id}/reject` | Payment status → `Rejected`, reason stored |
| `PUT /api/payments/{id}` as Coordinator (was Rejected) | Status resets to `Pending` |
| `PUT /api/payments/{id}` as Admin (was Approved) | Status stays `Approved` |
| `DELETE /api/payments/{id}` (status = Approved, non-Admin) | Returns 403 |
| `GET /api/reports/payment-status/export` | Returns valid `.xlsx` file |
| `GET /api/residents/{id}/fees` + change fee + re-run report | Report shows correct historical fee |

---

---

# PHASE 5 — Supporting Modules & Operations

> **Goal:** Dashboard API, Homepage CMS, secure media file serving, audit logs, remaining community modules (Meetings, Organization, Property Listings, Posyandu).

---

## 5.1 Dashboard Aggregation API

- [ ] Create `DashboardController.cs`:
  - [ ] `GET /api/dashboard` — returns role-specific stats:
    - **Admin/Treasurer:** Total residents, pending payments count, monthly collection total, recent transactions, unpaid count
    - **Coordinator:** Block-specific stats (residents, unpaid this month, pending approvals)
    - **Resident:** Own payment status for current year, outstanding balance
- [ ] Create `IDashboardService` + `DashboardService`
- [ ] Use **compiled queries** or `.AsNoTracking()` for all aggregation reads — critical for 512 MB RAM
- [ ] Cache dashboard result per role per user for 5 minutes using `IMemoryCache`

---

## 5.2 Homepage CMS API

- [ ] Create `HomepageController.cs` with public + admin endpoints:
  - [ ] `GET /api/homepage` — **public, no auth** — returns hero, about, events, gallery
  - [ ] `POST /api/homepage/hero` — update hero (Admin only)
  - [ ] `POST /api/homepage/about` — update about section (Admin only)
  - [ ] `GET /api/homepage/events` — list events (public)
  - [ ] `POST /api/homepage/events` — create event (Admin)
  - [ ] `PUT /api/homepage/events/{id}` — update event (Admin)
  - [ ] `DELETE /api/homepage/events/{id}` — delete event (Admin)
  - [ ] `PATCH /api/homepage/events/{id}/feature` — pin as featured event (Admin)
- [ ] Store hero/about content in `settings` table (key-value pairs)
- [ ] Events and gallery stored as dedicated DB models (extend schema if needed)

---

## 5.3 Secure Private Media File Serving

> Replicates Laravel's private storage disk

- [ ] Create `MediaController.cs`:
  - [ ] `POST /api/media/upload` — upload file (authenticated), returns `mediaFileId`
  - [ ] `GET /api/media/{id}` — serve file (authenticated, checks ownership/permission)
  - [ ] `DELETE /api/media/{id}` — delete file and record
- [ ] Create `IFileStorageService` + `LocalFileStorageService`:
  - Save files to `/var/www/civicore/storage/private/{type}/{filename}`
  - Filename = `Guid.NewGuid() + extension` (prevents enumeration)
  - Never expose real file paths in API responses
- [ ] `GET /api/media/{id}` implementation:
  - Look up `MediaFile` by ID
  - Check caller is authorized (owns it, or has correct permission)
  - Stream file bytes with correct `Content-Type` header
  - Use `File(stream, mimeType)` — never redirect to file path
- [ ] Validate file types and size on upload (use settings: `max_file_size_mb`)

---

## 5.4 System Settings API

- [ ] Create `SettingController.cs`:
  - [ ] `GET /api/settings` — list all settings (Admin only)
  - [ ] `PUT /api/settings/{key}` — update setting value (Admin only)
- [ ] Create `ISettingService` with in-memory cache — settings are read frequently
- [ ] Settings to manage: pagination limits, `max_accounts_per_unit`, `single_session_enabled`, SMTP config, file upload limits

---

## 5.5 Meetings & Attendance Module

- [ ] Create `MeetingController.cs`:
  - [ ] `GET /api/meetings` — list meetings
  - [ ] `POST /api/meetings` — create meeting (Admin/Coordinator)
  - [ ] `PUT /api/meetings/{id}` — update
  - [ ] `DELETE /api/meetings/{id}` — delete
  - [ ] `GET /api/meetings/{id}/attendances` — list attendees
  - [ ] `POST /api/meetings/{id}/attendances` — mark attendance
  - [ ] `GET /api/meetings/{id}/images` — list meeting images
  - [ ] `POST /api/meetings/{id}/images` — upload meeting image

---

## 5.6 Organization Management Module

- [ ] Create `OrganizationController.cs`:
  - [ ] `GET /api/organization/periods` — list periods
  - [ ] `POST /api/organization/periods` — create period
  - [ ] `GET /api/organization/periods/{id}/positions` — list positions in period
  - [ ] `POST /api/organization/periods/{id}/positions` — assign member to position
  - [ ] `PUT /api/organization/positions/{id}` — update position

---

## 5.7 Property Listings Module

- [ ] Create `PropertyListingController.cs`:
  - [ ] `GET /api/properties` — list (public or authenticated)
  - [ ] `POST /api/properties` — create listing
  - [ ] `PUT /api/properties/{id}` — update
  - [ ] `DELETE /api/properties/{id}` — delete

---

## 5.8 Audit Logging

- [ ] Install NuGet: `Serilog.AspNetCore` + `Serilog.Sinks.File` + `Serilog.Sinks.SQLite`
- [ ] Create `AuditMiddleware.cs`:
  - Log: user ID, action (method + path), timestamp, IP address, response status
  - Exclude: `GET /api/dashboard`, `GET /api/homepage` (high-frequency reads)
- [ ] Create `AuditLogController.cs` (Admin only):
  - [ ] `GET /api/audit-logs` — paginated, filterable by user/date/action
- [ ] Store audit logs in a separate `audit_logs` table or Serilog SQLite sink
- [ ] Add sensitive data action logging in services for: payment approval/rejection, user activation, password reset, role changes

---

## 5.9 Production Hardening for $5 Lightsail

- [ ] Configure Kestrel limits in `appsettings.Production.json`:
  ```json
  "Kestrel": {
    "Limits": {
      "MaxConcurrentConnections": 50,
      "MaxRequestBodySize": 10485760
    }
  }
  ```
- [ ] Enable `Response Compression` middleware (Brotli/Gzip) for JSON responses
- [ ] Enable `OutputCache` or `ResponseCache` on read-heavy public endpoints (homepage)
- [ ] Add `IMemoryCache` with size limits: `SizeLimit = 50 * 1024 * 1024` (50 MB max cache)
- [ ] Create `Dockerfile` (optional) or write `deploy.sh` for Lightsail setup
- [ ] Set up **systemd service** for the API process:
  ```ini
  [Service]
  WorkingDirectory=/var/www/civicore-api
  ExecStart=/usr/bin/dotnet CiviCore.Api.dll
  Restart=always
  RestartSec=10
  Environment=ASPNETCORE_ENVIRONMENT=Production
  ```
- [ ] Set up **Nginx reverse proxy** to Kestrel on `localhost:5000`
- [ ] Set up **Certbot + Let's Encrypt** SSL
- [ ] Set up **daily SQLite backup cron** → copy `.db` file to `/backups/` or S3

---

## ✅ Phase 5 — Success Criteria

| Test | Expected Result |
|------|----------------|
| `GET /api/dashboard` as Admin | Returns correct aggregate stats |
| `GET /api/dashboard` as Resident | Returns only personal payment data |
| `GET /api/homepage` (no auth) | Returns 200 with public content |
| `POST /api/media/upload` (valid image) | Returns `mediaFileId`, file saved privately |
| `GET /api/media/{id}` (unauthenticated) | Returns 401 |
| `GET /api/media/{id}` (authenticated, wrong user) | Returns 403 |
| `GET /api/media/{id}` (authenticated, owner) | Streams file bytes correctly |
| `GET /api/audit-logs` as Resident | Returns 403 |
| RAM check via `htop` after 1 hour uptime | Under 350 MB total process memory |
| `dotnet publish -c Release` | Builds clean, no warnings |

---

---

## 📦 Final Technology Stack Reference

| Concern | Laravel (Old) | .NET Core 8 (New) |
|---------|--------------|-------------------|
| Framework | Laravel 12 | ASP.NET Core 8 |
| Language | PHP 8.2 | C# 12 |
| ORM | Eloquent | Entity Framework Core 8 |
| Database | MySQL → SQLite | SQLite |
| Auth | Laravel Auth + Sanctum | ASP.NET Core Identity + Cookies |
| OAuth | Laravel Socialite | AspNetCore.Authentication.Google |
| 2FA | pragmarx/google2fa | Otp.NET + QRCoder |
| Permissions | Custom middleware | Authorization Policies + Handlers |
| Excel Export | maatwebsite/excel | ClosedXML |
| Queue | Laravel Database Queue | `IHostedService` + BackgroundService |
| File Storage | Laravel Storage (private disk) | Custom `IFileStorageService` |
| Encryption | Laravel `encrypt()` | AesGcm (System.Security.Cryptography) |
| Logging | Laravel Pail + Daily log | Serilog + File sink |
| API Docs | (none) | Swashbuckle (Swagger UI) |
| Mapping | Manual / Eloquent Resources | AutoMapper |
| Testing | PHPUnit | xUnit + Moq |
| Frontend | React 18 + Vite + Tailwind | **Unchanged** ✅ |

---

## ⏱️ Estimated Timeline

| Phase | Estimated Duration |
|-------|-------------------|
| Phase 1 — Base Architecture | 3–5 days |
| Phase 2 — Auth & User Management | 1–2 weeks |
| Phase 3 — Master Data | 1 week |
| Phase 4 — CiviPay Financial Module | 2–3 weeks |
| Phase 5 — Supporting Modules | 1–2 weeks |
| **Total** | **~6–9 weeks** |

---

*Generated: June 2026 · Based on CiviCore codebase analysis: 52 migrations, 23 controllers, 19 models, composer.json, package.json*
