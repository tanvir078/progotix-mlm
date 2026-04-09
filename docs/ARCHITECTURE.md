# MLM Architecture

## Product shape

ProgotiX is structured as a Laravel monolith with three main surfaces:

- Public acquisition: landing page and referral-aware registration
- Member operations: dashboard, network, binary tree, withdrawals, invoices, earnings
- Admin operations: members, plan management, withdrawal queue, invoices, reports

## Domain modules

- Identity and onboarding
  - `User`
  - `CreateNewUser`
  - `BinaryTreeService`
- Plan activation and commission engine
  - `MlmPlan`
  - `MlmSubscription`
  - `MlmActivationService`
  - `BinaryBonusService`
  - `MlmTransaction`
- Billing and payout operations
  - `MlmInvoice`
  - `MlmWithdrawalRequest`
  - `SimplePdfService`
- Admin oversight and reporting
  - `Admin/*Controller`

## Current strengths

- Core MLM transactions already live in services instead of controllers
- Binary placement and bonus distribution are separated from page rendering
- Member and admin areas already have distinct routing boundaries

## Current architecture gaps

- Navigation and information architecture were duplicated across layout files
- Brand and dashboard language were spread across views instead of shared config
- Dashboard pages mix strong business value with ad hoc UI structure

## Step-by-step improvement track

1. Shared IA and design tokens
   - Centralize navigation, labels, brand metadata, and shell styling
2. Dashboard composition
   - Move dashboard sections to reusable cards and metrics partials
3. Domain read models
   - Introduce dedicated query/data builder classes for member/admin dashboards
4. Workflow modules
   - Split withdrawals, invoices, and reporting into dedicated application services
5. Production operations
   - Queue jobs, audit logs, notifications, and scheduled payout/report tasks

## Immediate rule of thumb

- Controllers should gather page data, not implement commission logic
- Services should mutate MLM state
- Models should express relationships and small domain helpers
- Views should consume shared navigation and reusable presentation blocks
