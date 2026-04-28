# API Refactor Design — Dame Ulos Backend

**Date:** 2026-04-28  
**Branch:** backend-dameUlos  
**Scope:** All API controllers, route files, base Controller

---

## Goal

Remove duplication and fix bugs across all API controllers without changing any JSON response format or route URLs (which the frontend depends on).

---

## Section 1: Base Controller

**File:** `app/Http/Controllers/Controller.php`

Move the `checkAuth()` method from every individual controller into the base `Controller` class as a `protected` method. All controllers extend this class, so they inherit it automatically. Call sites (`if ($resp = $this->checkAuth()) return $resp;`) remain identical.

The method body stays exactly as-is:

```php
protected function checkAuth()
{
    if (!Auth::check()) {
        return response()->json([
            'code'    => 401,
            'message' => 'Unauthorized. Please login.',
            'data'    => null
        ], 401);
    }
    return null;
}
```

---

## Section 2: Controllers

**Files:** All files in `app/Http/Controllers/Api/`

Changes per controller (no response format or business logic changes):

1. **Delete `private function checkAuth()`** from every controller — now inherited from base.
2. **Remove double-auth checks** — `PengirimanBarangTController::update()`, `destroy()`, and `updateStatus()` call `$this->checkAuth()` at the top and then repeat `if (!Auth::check())` inside the method body. Remove the redundant inner check.
3. **Fix hardcoded ID bug** — `LiveOrderTController::destroy()` sets `$order->delete_id = 3;` (hardcoded). Change to `$order->delete_id = Auth::id();`.
4. **Remove dead commented-out code** — leftover commented blocks inside controller methods.
5. **Remove what-not-why comments** — inline comments like `// tambahkan create_id`, `// soft delete`, `// simpan siapa yang delete` that describe self-evident code.

---

## Section 3: Routes

**Files:** All files in `routes/api/`

- Route URLs are **not changed** — the frontend depends on them.
- Remove commented-out route lines for cleanliness.

---

## What Does NOT Change

- All JSON response bodies and HTTP status codes stay identical.
- All route URLs stay identical.
- All model definitions stay identical.
- All business logic (queries, stock management, customer upsert, etc.) stays identical.
