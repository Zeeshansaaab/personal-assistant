# Tailwind CSS Setup Verification

## Quick Check

If Tailwind isn't working, follow these steps:

### 1. Install Dependencies
```bash
cd backend
npm install
```

### 2. Verify Tailwind is Installed
```bash
npm list tailwindcss
```

### 3. Check Vite is Running
Make sure you're running:
```bash
npm run dev
```

### 4. Verify CSS Import
Check that `resources/css/app.css` is imported in `resources/js/app.js`:
```javascript
import '../css/app.css';
```

### 5. Check Tailwind Config
The `tailwind.config.js` should include:
```javascript
content: [
  "./resources/**/*.blade.php",
  "./resources/**/*.js",
  "./resources/**/*.vue",
]
```

### 6. Rebuild Assets
```bash
# Stop Vite (Ctrl+C)
# Clear cache and restart
rm -rf node_modules/.vite
npm run dev
```

## Testing Tailwind

Add this test to see if Tailwind works:

```html
<div class="bg-red-500 text-white p-4">
  If this is red, Tailwind is working!
</div>
```

## Common Issues

1. **Tailwind classes not applying?**
   - Check browser console for errors
   - Verify `@vite(['resources/css/app.css', 'resources/js/app.js'])` in blade file
   - Make sure Vite dev server is running

2. **Styles look broken?**
   - Hard refresh browser (Ctrl+Shift+R / Cmd+Shift+R)
   - Clear browser cache
   - Restart Vite dev server

3. **Build not working?**
   ```bash
   npm run build
   ```
   Check for errors in the output

## Verify Setup

Run this command to check if everything is configured:
```bash
cd backend
npm list tailwindcss postcss autoprefixer
```

All three should be listed. If not, run:
```bash
npm install -D tailwindcss postcss autoprefixer
```

