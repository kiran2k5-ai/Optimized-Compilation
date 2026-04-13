## 🚀 Quick Start - CodeRunner + Pyodide

**5-minute setup guide for experienced developers**

---

## 📁 What You Get

5 functional files + comprehensive documentation:

```
coderunner/
├── enable_pyodide.php        ← Configuration
├── jobe_api_mock.php         ← API wrapper
├── pyodide_executor.js       ← Executor engine
├── setup_pyodide.php         ← Initializer
└── renderer.php              ← (modified)
```

---

## ⚡ Install in 3 Steps

### 1. Copy Files
```
Put these files in: public/question/type/coderunner/
- enable_pyodide.php
- jobe_api_mock.php
- pyodide_executor.js
- setup_pyodide.php
```

### 2. Modify Renderer
```
Edit: renderer.php
Add lines provided in renderer_modifications.txt
(Inject executor script, add execute button)
```

### 3. Run Setup
```
Browser: http://localhost/question/type/coderunner/setup_pyodide.php
OR CLI: php setup_pyodide.php
```

---

## ✅ Done!

Your CodeRunner now:
- ✨ Executes Python locally in browser
- 🚫 Doesn't need Jobe server
- ⚡ Shows results instantly
- 💾 Saves to Moodle automatically

---

## 🧪 Test It

1. Create CodeRunner question
2. Add test case
3. Student writes: `print("Hello")`
4. Click Execute → Runs in browser
5. Click Submit → Saves to Moodle

---

## 🔧 Config (Optional)

Edit `enable_pyodide.php`:
```php
define('PYODIDE_VERSION', '0.23.0');
define('PYODIDE_TIMEOUT', 30);
define('PYODIDE_MAX_OUTPUT', 1000000);
define('PYODIDE_DEBUG', false);
```

---

## 📚 Documentation

- **Full Setup:** See INSTALLATION_GUIDE.md
- **User Guide:** See PYODIDE_INTEGRATION.md
- **Dev Reference:** See DEVELOPER_REFERENCE.md
- **Issues:** Check Troubleshooting section

---

## 🆘 Quick Troubleshooting

| Issue | Fix |
|-------|-----|
| "Pyodide not defined" | Check internet, F12 console |
| Slow first load | Normal (~20-30s), refresh after |
| Code won't execute | Enable PYODIDE_DEBUG, check console |
| Results not saving | Check Moodle database, AJAX enabled |
| CDN blocked | Check firewall, CORS headers |

---

## 📞 Need Help?

1. Check PYODIDE_INTEGRATION.md for detailed docs
2. Open F12 console for debug output
3. Set PYODIDE_DEBUG = true
4. Review DEVELOPER_REFERENCE.md for technical details
5. Check Moodle logs: Admin → Reports → Logs

---

## 📊 What's Different From Jobe?

| Feature | Jobe | Pyodide |
|---------|------|---------|
| Server | External required | None (browser) |
| Execution Location | Remote | Local (student PC) |
| Speed | Network dependent | ~0.5 seconds |
| Setup | Complex | 5 minutes |
| Maintenance | Required | Minimal |
| Offline | No | Yes (after load) |

---

## ✨ Features Included

✅ Python 3.11 in browser
✅ Full stdlib support
✅ Instant execution feedback
✅ Auto-grading integration
✅ Result persistence
✅ Error reporting
✅ Input/output capture
✅ Timeout handling

---

## 📋 Pre-Check

Before installing:
- [ ] Moodle 4.0+
- [ ] CodeRunner plugin installed
- [ ] PHP 7.4+
- [ ] Admin access
- [ ] Browser with WebAssembly support

---

**Total time to working system: ~10 minutes**

Enjoy faster, offline code execution! 🎉

---

*Version: 1.0*
*No Jobe server? No problem!*
