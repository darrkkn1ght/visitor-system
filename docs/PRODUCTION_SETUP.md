# Production Setup & Operations Guide

## 1. Running the Realtime Server

The Visitor System includes a Node.js server (`/realtime`) that handles instant notifications. This application **must be running** for the following features to work:
- Instant Admin Availability updates on the visitor page.
- Live "New Message" badges in the Admin Dashboard.
- Queue syncing between admin tabs.

### Option A: The "Quick Start" Method (Recommended for simple local setup)

1.  Locate the file `run_realtime_server.bat` in the root folder (`c:\xampp\htdocs\visitor-system`).
2.  Double-click it.
3.  A black command window will open. **Keep this window open.** You can minimize it.
4.  If the computer restarts, you just need to double-click this file again.

> **Tip:** You can place a shortcut to this `.bat` file in your Windows "Startup" folder to have it run automatically when the computer turns on.
> - Press `Win + R`, type `shell:startup`, and press Enter.
> - Drag a **Shortcut** of `run_realtime_server.bat` into this folder.

### Option B: The "Pro" Method (Using PM2)

If you want the server to run silently in the background (as a service) and restart automatically without a visible window:

1.  **Install PM2** (Process Manager):
    ```powershell
    npm install -g pm2
    ```

2.  **Start the server**:
    ```powershell
    cd c:\xampp\htdocs\visitor-system\realtime
    pm2 start server.js --name "visitor-realtime"
    ```

3.  **Make it persistent** (Starts on Windows boot):
    ```powershell
    pm2 save
    npm install pm2-windows-startup -g
    pm2-startup install
    ```
    *(Note: Process for Windows startup might vary slightly depending on permissions).*

---

## 2. Firewall Settings

Since you are running this in a local organization network, other computers (e.g. iPads, other staff PCs) update via the network.

**Ensure Port 3005 is Open:**
1.  Open **Windows Defender Firewall with Advanced Security**.
2.  Go to **Inbound Rules** -> **New Rule**.
3.  Select **Port** -> **TCP**.
4.  Specific local ports: `3005`.
5.  **Allow the connection**.
6.  Name it: `Visitor System Realtime`.

**Ensure Port 80 (Apache) is Open:**
1.  Ensure standard HTTP traffic (Port 80) is also allowed so others can access the PHP site.

---

## 3. Maintenance

### Logs
- **PHP/Apache**: Check XAMPP Control Panel logs.
- **Realtime Server**:
    - If using **Option A**, errors appear in the black window.
    - If using **Option B (PM2)**, run: `pm2 logs visitor-realtime` or `pm2 monit`.

### Updating the Code
If you pull new code from GitHub:
1.  **PHP Changes**: Apply instantly (refresh browser).
2.  **Realtime Server Changes**:
    - **Option A**: Close the black window and run `run_realtime_server.bat` again.
    - **Option B**: Run `pm2 restart visitor-realtime`.

### Database Backups
- Continue using `http://localhost/visitor-system/admin_dashboard.php` -> Settings -> Backup Database.
- Regular manual backups are recommended (e.g., weekly).
