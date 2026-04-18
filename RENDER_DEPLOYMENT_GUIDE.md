# Render Deployment Guide - Moodle + PostgreSQL Database

## 🎯 Quick Setup Steps

### Step 1: Prepare Your Git Repository
```bash
cd e:\moodel_xampp\htdocs\moodle
git add -A
git commit -m "Fix: Add PostgreSQL support for Render deployment"
git push origin main
```

### Step 2: Create Render Database Service

1. Go to **Render Dashboard**: https://dashboard.render.com
2. Click **"New +"** → **"PostgreSQL"**
3. Fill in:
   - **Name**: `coderunner-db` (IMPORTANT - must match render.yaml)
   - **Database**: `moodle`
   - **User**: `moodle`
   - **Region**: Same as your web service
   - **Plan**: Free tier (or Starter $15/month)
4. Click **"Create Database"**
5. **Wait 2-3 minutes** for database to be ready
6. Copy the **Internal Database URL** (you'll need this)

### Step 3: Create Render Web Service

1. Go to **Render Dashboard**
2. Click **"New +"** → **"Web Service"**
3. **Connect Repository**:
   - Select your GitHub/GitLab repo
   - Branch: `main`
4. **Configuration**:
   - **Name**: `coderunner-academy`
   - **Runtime**: `Docker`
   - **Region**: Same as database
   - **Plan**: Free (or Starter)
5. **Environment Variables** (ADD THESE):
   ```
   MOODLE_URL = https://coderunner-academy.onrender.com
   DEBUG_MODE = false
   CODERUNNER_ENABLED = true
   ```
6. **Click "Create Web Service"**

### Step 4: Link Database to Web Service

1. In Web Service settings, go to **"Environment"**
2. Click **"Add Environment Variable"**
3. **Name**: `DATABASE_URL`
4. **Value**: Click **"Reference an existing resource"**
5. Select **`coderunner-db`** → Choose **"Internal Database URL"**
6. Click **"Save Changes"**

### Step 5: Deploy

1. Render will auto-build and deploy when you push
2. Check **"Logs"** tab to watch deployment
3. Wait for status: ✓ **"Live"** (green)
4. Click the URL to open your Moodle site

---

## ✅ What We Fixed

### Problem: PostgreSQL Connection Failed
**Cause**: Missing `pdo_pgsql` PHP extension in Dockerfile

**Solution**: 
- Added `libpq-dev` to system dependencies
- Added `pdo_pgsql` to PHP extensions
- Improved config.php PostgreSQL handling

### Files Changed:
1. **Dockerfile**: Added PostgreSQL support
   ```dockerfile
   libpq-dev \           # PostgreSQL development libs
   pdo_pgsql \           # PostgreSQL PHP driver
   ```

2. **config.php**: Better database detection
   ```php
   // Different settings for PostgreSQL vs MySQL
   if (PostgreSQL) {
       // No collation
   } else {
       // MySQL collation settings
   }
   ```

3. **Trusted Domains**: Added Render domain
   ```php
   'coderunner-academy.onrender.com'
   ```

---

## 🔍 Verify Deployment

### Check Database Connection:
1. Go to your Render site: `https://coderunner-academy.onrender.com`
2. You should see Moodle loading
3. If database error appears:
   - Check **Logs** in Render dashboard
   - Look for `DATABASE_URL` error

### View Logs:
```
Render Dashboard → Your Web Service → Logs
```

Look for:
- ✓ `✓ PostgreSQL is ready!` = Database connected
- ✗ `Connection refused` = Database not linked
- ✗ `Invalid DATABASE_URL` = Wrong format

---

## 🚨 Common Issues & Fixes

### Issue 1: "Invalid DATABASE_URL"
**Cause**: Database not linked to web service

**Fix**:
1. Go to Web Service settings
2. Environment tab
3. Make sure `DATABASE_URL` is set
4. Redeploy: Click **"Manual Deploy"**

### Issue 2: "Cannot connect to database"
**Cause**: PostgreSQL extension missing

**Fix**:
- Already done! Dockerfile now includes `pdo_pgsql`
- Just redeploy

### Issue 3: "SSL connection required"
**Cause**: Render PostgreSQL requires SSL

**Fix**: Add to config.php:
```php
$CFG->dboptions = array (
    'dbpersist' => 0,
    'dbport' => '',
    'sslmode' => 'require',  // Add this
);
```

### Issue 4: Database is empty (installation required)
**Cause**: First deployment needs Moodle installation

**Fix**:
1. Visit: `https://coderunner-academy.onrender.com/install.php`
2. Follow installation wizard
3. Creates all tables in PostgreSQL
4. Should complete in 2-5 minutes

---

## 📋 Render Service Settings

### Web Service: `coderunner-academy`
| Setting | Value |
|---------|-------|
| Docker | ✓ Enabled |
| Dockerfile | `./Dockerfile` |
| Build Command | Auto (docker build) |
| Start Command | Auto (Apache) |
| Port | 3000 |
| Region | Oregon (or your choice) |
| Plan | Free / Starter |
| Disk | moodledata 1GB |

### Database Service: `coderunner-db`
| Setting | Value |
|---------|-------|
| Type | PostgreSQL 15 |
| Database | moodle |
| User | moodle |
| Region | Same as web service |
| Plan | Free / Starter |
| Backups | Daily (paid plans) |

---

## 🔐 Security Checklist

- [ ] Database password is auto-generated (Render)
- [ ] DATABASE_URL is secret (environment variable)
- [ ] SSL enabled for database
- [ ] HTTPS for web service (Render auto-provides)
- [ ] config.php doesn't have hardcoded passwords
- [ ] Git doesn't contain DATABASE_URL

---

## 📝 Important Files

### Dockerfile
- Installs PHP 8.2 Apache
- Adds PostgreSQL + MySQL support
- Configures Moodle
- Sets up moodledata volume

### render.yaml
- Defines services
- Links database
- Sets environment variables
- Auto-deploys on git push

### config.php
- Detects DATABASE_URL
- Configures PostgreSQL
- Sets trusted domains

---

## 🚀 Next Steps

1. **Git commit and push**:
   ```bash
   git add Dockerfile config.php
   git commit -m "Add PostgreSQL support for Render"
   git push origin main
   ```

2. **Create services on Render** (follow Step 1-5 above)

3. **Watch deployment**:
   - Render builds Docker image
   - Starts web service
   - Connects to database
   - Should be live in 5-10 minutes

4. **Run installation**:
   - Visit `/install.php`
   - Follow wizard
   - Database tables created

5. **Login and use**:
   - Username: `admin`
   - Password: (from installation)

---

## 📞 Troubleshooting

### Still getting database error?

1. Check Render logs:
   ```
   Dashboard → Web Service → Logs
   ```

2. Verify DATABASE_URL is set:
   ```
   Dashboard → Web Service → Environment
   Look for: DATABASE_URL
   ```

3. Check database is running:
   ```
   Dashboard → Database Service → Status
   Should show: Available (green)
   ```

4. Restart web service:
   ```
   Dashboard → Web Service → Manual Deploy
   ```

---

## ✨ Success Indicators

When everything works:
- ✓ Render shows "Live" (green)
- ✓ Can access: `https://coderunner-academy.onrender.com`
- ✓ Moodle loads without errors
- ✓ Logs show: `✓ PostgreSQL is ready!`
- ✓ Can login with admin account

---

**Last Updated**: April 18, 2026
**Status**: Ready for Render deployment
