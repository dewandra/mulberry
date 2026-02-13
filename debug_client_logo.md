# Client Logo Debug Guide

## Quick Debugging Steps

Buka **Browser Console** (F12 → Console tab) dan jalankan command berikut untuk melihat data:

```javascript
// Check if client data exists
console.log("User:", window.$page.props.auth.user);
console.log("Client:", window.$page.props.auth.user?.client);
console.log("Client Logo:", window.$page.props.auth.user?.client?.logo);
console.log(
    "Company Name:",
    window.$page.props.auth.user?.client?.company_name,
);
```

## Expected Output

### If user is Client/PIC with logo:

```javascript
User: {id: "...", full_name: "Aji!", role: "client", client_id: "...", client: {...}}
Client: {id: "...", company_name: "AUTO PART", logo: "http://127.0.0.1:8000/storage/clients/logos/..."}
Client Logo: "http://127.0.0.1:8000/storage/clients/logos/..."
Company Name: "AUTO PART"
```

### If logo is null:

```javascript
Client Logo: null
```

## Common Issues

1. **User client_id is null** → User tidak terhubung ke client
2. **Client logo is null** → Client belum upload logo
3. **Logo URL broken** → File tidak ada di storage atau symlink belum dibuat

## What to check:

1. Apakah user Aji memiliki `client_id` yang terisi?
2. Apakah client AUTO PART sudah punya logo yang di-upload?
3. Buka Network tab → Reload page → Lihat di request headers apakah data client ter-load

---

**Next Steps:**
Jalankan debug command di console dan beritahu saya hasilnya.
