# Main Menu Blueprint

## Member menu

### Dashboard

- Controller: `App\Http\Controllers\DashboardController`
- View: `resources/views/mlm/dashboard.blade.php`
- Purpose: operating summary, quick actions, momentum, and next-step guidance
- Key sections:
  - hero summary
  - KPI cards
  - module workspace map
  - recent earnings
  - recent invoices

### Referral Network

- Controller: `App\Http\Controllers\NetworkController`
- View: `resources/views/mlm/network.blade.php`
- Purpose: direct and second-level structure
- Key logic:
  - sponsor relation
  - direct referral count
  - second-level visibility

### Packages

- Controller: `App\Http\Controllers\PlanController`
- View: `resources/views/mlm/plans.blade.php`
- Purpose: activation and qualification
- Key logic:
  - active plan state
  - activation CTA
  - plan history

### Shop

- Controller: `App\Http\Controllers\ShopController`
- View: `resources/views/mlm/shop.blade.php`
- Purpose: retail commerce and commissionable volume
- Key logic:
  - featured products
  - retail commission rate visibility
  - order-volume guidance

### Earnings

- Controller: `App\Http\Controllers\EarningsController`
- View: `resources/views/mlm/earnings.blade.php`
- Purpose: traceable income ledger

### Binary Tree

- Controller: `App\Http\Controllers\BinaryTreeController`
- View: `resources/views/mlm/binary-tree.blade.php`
- Purpose: placement and team-volume monitoring

### Withdrawals

- Controller: `App\Http\Controllers\WithdrawalController`
- View: `resources/views/mlm/withdrawals.blade.php`
- Purpose: payout request workflow

### Invoices

- Controller: `App\Http\Controllers\InvoiceController`
- View: `resources/views/mlm/invoices.blade.php`
- Purpose: billing records and PDF export

## Admin menu

### Admin Dashboard

- Controller: `App\Http\Controllers\Admin\AdminDashboardController`
- Purpose: executive overview

### Members

- Controllers:
  - `App\Http\Controllers\Admin\MemberManagementController`
  - `App\Http\Controllers\Admin\MemberCrudController`
- Purpose: lifecycle control

### Plan Management

- Controller: `App\Http\Controllers\Admin\PlanManagementController`
- Purpose: compensation plan governance

### Tree Manager

- Controller: `App\Http\Controllers\Admin\AdminBinaryTreeController`
- Purpose: structure oversight

### Withdrawal Queue

- Controller: `App\Http\Controllers\Admin\WithdrawalManagementController`
- Purpose: payout operations

### Invoices

- Controller: `App\Http\Controllers\Admin\InvoiceManagementController`
- Purpose: billing oversight

### Reports

- Controller: `App\Http\Controllers\Admin\ReportController`
- Purpose: analytics and operational health
