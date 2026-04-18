# Render Deployment Checklist - Moodle + PostgreSQL

## ✅ Code Changes Complete

- [x] Fixed Dockerfile: Added `pdo_pgsql` PostgreSQL support
- [x] Updated config.php: Better PostgreSQL detection and handling
- [x] Added Render domain to trusted hosts
- [x] Created .gitignore: Exclude sensitive local files
- [x] Pushed to GitHub: Ready for Render auto-deployment

**Current Git Status**: ✓ Pushed to `main` branch
**Commit**: `afe2c9c6` - "Fix: Add PostgreSQL support and Render deployment configuration"

---

## 🚀 NEXT: Deploy to Render

### STEP 1: Create PostgreSQL Database on Render
**Do this FIRST before creating web service**

1. Go to: https://dashboard.render.com
2. Click **"New +"** → **"PostgreSQL"**
3. Configure:
   - **Name**: `coderunner-db` ⚠️ **MUST MATCH render.yaml**
   - **Database**: `moodle`
   - **User**: `moodle`
   - **Region**: Choose your region
   - **Plan**: `Free` (free tier available)
4. Click **"Create Database"**
5. **WAIT 2-3 minutes** for it to initialize
6. **COPY** the "Internal Database URL" (you'll need this in Step 2)
   - Format: `postgresql://user:password@host:5432/dbname`

**Status**: Database should show "Available" ✓

---

### STEP 2: Create Web Service on Render
**Do this AFTER database is ready**

1. Go to: https://dashboard.render.com
2. Click **"New +"** → **"Web Service"**
3. **Connect your repository**:
   - Select your GitHub repo: `Optimized-Compilation`
   - Branch: `main`
   - Click **"Connect"**
4. **Configure Web Service**:
   - **Name**: `coderunner-academy`
   - **Runtime**: `Docker`
   - **Region**: ⚠️ **SAME as database** (important!)
   - **Plan**: `Free` or `Starter` ($7/month)
5. Click **"Advanced"** (expand)
6. **Disk**: 
   - Add disk for moodledata
   - **Mount Path**: `/var/moodledata`
   - **Size**: 1 GB
7. Click **"Create Web Service"**

**Status**: Render will start building Docker image...

---

### STEP 3: Link Database to Web Service
**Do this while web service is building (or after)**

1. In your **Web Service** dashboard
2. Go to **"Environment"** tab
3. Click **"Add Environment Variable"**
4. **Name**: `DATABASE_URL`
5. **Value**: Click **"Reference an existing resource"**
   - Select your database: `coderunner-db`
   - Choose: **"Internal Database URL"**
6. Click **"Save Changes"**
7. Render will **auto-redeploy** with the DATABASE_URL

**Status**: Service will redeploy automatically

---

### STEP 4: Wait for Deployment to Complete

Check the **Logs** tab:
```
✓ Building Docker image...
✓ Pushing image to registry...
✓ Starting service...
✓ PostgreSQL is ready!
✓ Service is live
```

**Expected time**: 5-10 minutes

---

### STEP 5: Install Moodle (First Time Only)

1. Wait for status to show: **"Live"** ✓ (green)
2. Click the **URL** to visit your site
   - `https://coderunner-academy.onrender.com`
3. You'll see the Moodle **installation page**
4. Follow the installation wizard:
   - Confirm environment ✓
   - Create admin account (set password!)
   - Configure site settings
   - This takes **2-5 minutes**

**Result**: Database tables created in PostgreSQL ✓

---

### STEP 6: Login and Use

After installation completes:
1. Go to: `https://coderunner-academy.onrender.com`
2. Login with:
   - **Username**: `admin`
   - **Password**: (from Step 5)
3. You're live! 🎉

---

## 🔍 Verify Everything Works

After deployment, check:

| Item | Should See | Status |
|------|-----------|--------|
| Render Web Service | "Live" (green) | ✓ |
| Render Database | "Available" (green) | ✓ |
| Website loads | Moodle homepage or install.php | ✓ |
| Database connected | No database errors | ✓ |
| Logs | "PostgreSQL is ready!" | ✓ |

---

## ⚠️ Important Details

### Database Name Must Match
- **render.yaml** references: `coderunner-db`
- **You must create** database service named: `coderunner-db`
- ❌ If you name it differently, deployment will fail

### Region Must Match
- Web service region
- Database region
- Must be the **SAME region** for reliability

### DATABASE_URL Format
Should look like:
```
postgresql://moodle:password@host.render.com:5432/moodle
```

Render automatically injects this as `DATABASE_URL` environment variable.

---

## 🚨 If Deployment Fails

### Check Logs First
1. **Web Service** → **Logs** tab
2. Look for error messages
3. Common issues:

**Error**: `Cannot find database coderunner-db`
- **Fix**: Database name must be `coderunner-db` exactly
- Create database with correct name

**Error**: `Connection refused` or `Connection timeout`
- **Fix**: Database not ready
- Wait 2-3 minutes and try redeploying
- Check database shows "Available"

**Error**: `pdo_pgsql not found`
- **Fix**: Already done! Docker image has it
- Just redeploy: Manual Deploy button

**Error**: `DATABASE_URL not set`
- **Fix**: Go to Environment tab
- Add DATABASE_URL reference to database
- Redeploy

---

## 📋 Checklist Before Deploying

- [x] Code pushed to GitHub (`main` branch)
- [x] Dockerfile has PostgreSQL support
- [x] config.php configured for Render
- [ ] PostgreSQL database created on Render (name: `coderunner-db`)
- [ ] Web service created on Render (name: `coderunner-academy`)
- [ ] DATABASE_URL linked to web service
- [ ] Same region for web service and database
- [ ] Ready to deploy!

---

## 🎯 Your URLs

Once deployed:
- **Main Site**: https://coderunner-academy.onrender.com
- **Admin Login**: https://coderunner-academy.onrender.com/login
- **phpMyAdmin** (optional, not included): Admin only

---

## 💰 Cost Estimate

| Service | Tier | Cost |
|---------|------|------|
| Web Service | Free | FREE (0-40 requests/hour) |
| Web Service | Starter | $7/month |
| PostgreSQL DB | Free | FREE (very limited) |
| PostgreSQL DB | Starter | $15/month |
| **Total** | **Free** | **FREE** |
| **Total** | **Basic** | **~$22/month** |

For learning/testing: Use Free tier
For production: Use Starter tier

---

## 📞 Support

### Render Support
- Docs: https://render.com/docs
- Status: https://status.render.com
- Support: contact@render.com

### Moodle Support
- Docs: https://docs.moodle.org
- Forums: https://moodle.org/forums

---

## ✨ Success Looks Like

When everything works:
```
✓ Render shows "Live"
✓ PostgreSQL shows "Available"
✓ Can visit: https://coderunner-academy.onrender.com
✓ Sees Moodle or installation page
✓ Logs show: "PostgreSQL is ready!"
✓ Installation wizard runs
✓ Can login after setup
```

---

## 🎉 Ready to Deploy!

Your code is ready for Render. Just follow the steps above to:
1. Create PostgreSQL database
2. Create web service
3. Link them together
4. Deploy!

**Good luck! 🚀**

---

**Last Updated**: April 18, 2026
**Status**: ✓ Ready for Production Deployment
