# Setup & Testing Instructions

## ✅ Your System is Ready!

The OOP structure has been implemented and is **backward compatible** with your existing code.

---

## 🚀 How to Test

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**

### Step 2: Test Database Connection
1. Open your browser
2. Go to: `http://localhost/My%20newwebsite/test_connection.php`
3. This will show you:
   - ✅ If config.php loads
   - ✅ If Database class works
   - ✅ If database connection is successful
   - ✅ If all classes are working
   - ✅ If backward compatibility is maintained

### Step 3: Test Your Existing Pages
1. **Home Page**: `http://localhost/My%20newwebsite/index.php`
2. **Search**: `http://localhost/My%20newwebsite/search.php`
3. **Browse**: `http://localhost/My%20newwebsite/browse.php`
4. **Upload**: `http://localhost/My%20newwebsite/upload.php`

### Step 4: Test API
1. **Resources API**: `http://localhost/My%20newwebsite/api.php?endpoint=resources`
2. **Search API**: `http://localhost/My%20newwebsite/api.php?endpoint=search&q=computer`
3. **Courses API**: `http://localhost/My%20newwebsite/api.php?endpoint=courses`
4. **Statistics API**: `http://localhost/My%20newwebsite/api.php?endpoint=statistics`

---

## 🔧 Database Configuration

Your database is currently configured for **localhost** (your PC):

```php
// classes/Database.php
$this->host = 'localhost';  // Your PC
$this->user = 'root';
$this->password = 'Uttorent@24';
$this->database = 'libraryms';
```

### To Switch to Database on Another Computer:

1. Edit `classes/Database.php`
2. Change the host:
   ```php
   $this->host = '192.168.1.100';  // IP address of other computer
   // OR
   $this->host = 'database-server.local';  // Hostname of other computer
   ```
3. Make sure:
   - MySQL is running on the other computer
   - MySQL allows remote connections
   - Firewall allows port 3306
   - User has remote access permissions

---

## ✅ Backward Compatibility

**Your existing files will continue to work!**

All your existing files that use `$conn` directly (like `index.php`, `browse.php`, etc.) will work because:

1. `config.php` still provides `$conn` variable
2. `$conn` is a mysqli object (same as before)
3. All existing queries will work

**Example:**
```php
// This still works (your existing code)
$result = $conn->query("SELECT * FROM faculties");
```

**New OOP way (optional, for new code):**
```php
$db = Database::getInstance();
$result = $db->query("SELECT * FROM faculties", []);
```

---

## 🐛 Troubleshooting

### If test_connection.php shows errors:

1. **"Class 'Database' not found"**
   - Check if `classes/Database.php` exists
   - Check if autoloader in `config.php` is working

2. **"Connection FAILED"**
   - Check if MySQL is running in XAMPP
   - Verify database name is `libraryms`
   - Check username/password in `classes/Database.php`

3. **"Table doesn't exist"**
   - This is OK if you haven't created tables yet
   - Your existing database should have the tables

### If existing pages don't work:

1. Check browser console for JavaScript errors
2. Check Apache error logs
3. Make sure `config.php` is loading correctly
4. Verify database connection

---

## 📋 What Changed

### New Files Created:
- ✅ `classes/Database.php` - OOP database connection
- ✅ `classes/API.php` - RESTful API handler
- ✅ `classes/DataProcessor.php` - Data processing
- ✅ `test_connection.php` - Test file

### Files Modified:
- ✅ `config.php` - Now uses OOP with autoloader
- ✅ `api.php` - Now uses OOP API class

### Files NOT Changed (Still Work):
- ✅ `index.php` - Works as before
- ✅ `browse.php` - Works as before
- ✅ `search.php` - Works as before
- ✅ `upload.php` - Works as before
- ✅ All other existing files - Work as before

---

## 🎯 Quick Test Checklist

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] `test_connection.php` shows all ✅
- [ ] `index.php` loads correctly
- [ ] `api.php?endpoint=resources` returns JSON
- [ ] Existing pages work normally

---

## 💡 Next Steps

1. **Test locally first** (localhost)
2. **Verify everything works**
3. **Then switch to remote database** (if needed)
4. **Test again with remote database**

---

## 📞 Need Help?

If something doesn't work:
1. Check `test_connection.php` output
2. Check browser console (F12)
3. Check Apache error logs
4. Verify database credentials

**Your system should work exactly as before, but now with OOP structure!** 🚀

