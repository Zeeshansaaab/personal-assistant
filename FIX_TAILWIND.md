# Fix Tailwind CSS Not Working

## The Problem
Tailwind classes like `w-full`, `max-w-md`, etc. are not applying styles.

## Solution Steps

### 1. Restart Vite Dev Server
```bash
cd backend
# Stop current server (Ctrl+C)
npm run dev
```

### 2. Clear Browser Cache
- Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
- Or open DevTools → Application → Clear Storage → Clear site data

### 3. Verify Setup

**Check if CSS is being processed:**
1. Open browser DevTools (F12)
2. Go to Network tab
3. Reload page
4. Look for `app.css` file
5. Click on it and check if it contains Tailwind utility classes

**If app.css is empty or doesn't have Tailwind classes:**

### 4. Rebuild Assets
```bash
cd backend
# Stop Vite
rm -rf node_modules/.vite
rm -rf public/build
npm run dev
```

### 5. Check Console for Errors
Open browser console (F12) and look for:
- CSS loading errors
- Vite HMR errors
- Any red error messages

### 6. Verify Files Are Correct

**Check `resources/css/app.css` has:**
```css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
```

**Check `resources/js/app.js` imports CSS:**
```javascript
import '../css/app.css';
```

**Check `resources/views/app.blade.php` has:**
```php
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### 7. Test Tailwind is Working

Add this test div to LoginForm.vue temporarily:
```vue
<div class="bg-red-500 text-white p-4 mb-4">
  TAILWIND TEST - If this is red, Tailwind works!
</div>
```

If it's red → Tailwind is working, issue is with specific classes
If it's not red → Tailwind is not processing

### 8. If Still Not Working

**Option A: Use CDN (Temporary Test)**
Add to `app.blade.php` in `<head>`:
```html
<script src="https://cdn.tailwindcss.com"></script>
```

If this works, the issue is with PostCSS/Vite processing.

**Option B: Check PostCSS Processing**
```bash
cd backend
npx postcss resources/css/app.css -o test-output.css
cat test-output.css
```

If this shows Tailwind classes, PostCSS is working.

### 9. Nuclear Option - Reinstall
```bash
cd backend
rm -rf node_modules package-lock.json
npm install
npm run dev
```

## Common Issues

1. **Vite not processing CSS**
   - Solution: Restart Vite, check vite.config.js

2. **PostCSS not configured**
   - Solution: Verify postcss.config.js exists and is correct

3. **CSS not imported**
   - Solution: Check app.js imports '../css/app.css'

4. **Browser cache**
   - Solution: Hard refresh or clear cache

5. **Tailwind config content paths**
   - Solution: Verify tailwind.config.js includes Vue files

## Quick Test Command

Run this to verify everything:
```bash
cd backend
npm list tailwindcss postcss autoprefixer && echo "✅ Dependencies OK" || echo "❌ Missing dependencies"
```

