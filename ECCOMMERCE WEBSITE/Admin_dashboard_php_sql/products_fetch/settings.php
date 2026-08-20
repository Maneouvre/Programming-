<?php
// Establish safe default properties fallback values for connection sessions
$host   = "localhost";
$dbname = "ecommerce_db";
$user   = "root";
$pass   = "";

$adminName = "Manoeuvre";
$avatarImg = "https://placehold.co";
$darkModeSetting = 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Check if an admin settings table exists, otherwise build or read it safely
    // For this unified baseline example, we assume you read the current user profiles state data rows
    // $stmt = $pdo->query("SELECT * FROM admin_settings LIMIT 1");
    // if($row = $stmt->fetch()) { ... }
} catch (PDOException $e) {
    // Graceful silent fallback setup configurations
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Settings - Dashboard</title>
  <link rel="stylesheet" href="products.css">
  <link rel="stylesheet" href="https://cloudflare.com">
  <style>
    /* Baseline extensions matching your products.css theme matrices */
    .settings-container { max-width: 600px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; transition: all 0.3s ease; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #4a5568; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box; font-size: 14px; background: inherit; color: inherit; }
    .avatar-preview-wrapper { display: flex; align-items: center; gap: 20px; margin-bottom: 10px; }
    .avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #3182ce; }
    .toggle-wrapper { display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #cbd5e0; border-radius: 4px; }
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e0; transition: .3s; border-radius: 24px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .3s; border-radius: 50%; }
    input:checked + .slider { background-color: #3182ce; }
    input:checked + .slider:before { transform: translateX(20px); }
    .save-settings-btn { background: #3182ce; color: #fff; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .save-settings-btn:hover { background: #2b6cb0; }
    
    /* SYSTEM DARK MODE STYLING POLICIES OVERLAYS */
    body.dark-theme { background-color: #1a202c !important; color: #e2e8f0 !important; }
    body.dark-theme .settings-container, body.dark-theme .sidebar { background-color: #2d3748 !important; border-color: #4a5568; }
    body.dark-theme .form-group label { color: #cbd5e0 !important; }
    body.dark-theme .form-control { border-color: #4a5568 !important; }
    body.dark-theme .toggle-wrapper { border-color: #4a5568 !important; }
    body.dark-theme .top-header { background-color: #2d3748 !important; border-color: #4a5568; }
  </style>
</head>
<body>

  <!-- SIDEBAR NAVIGATION NAVIGATION -->
  <aside class="sidebar">
    <div class="logo">
      <i class="fa-solid fa-cubes"></i>
      <span>Product Admin</span>
    </div>
    <div class="menu-section">
      <p class="section-title">General</p>
      <nav><a href="#" class="nav-item active">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="../products_fetch/products.php" class="nav-item">
                    <span class="nav-icon">📦</span> Products
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">📋</span> Orders
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">📈</span> Analytics
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">⚙️</span> Settings
                </a>
      </nav>
    </div>
  </aside>

  <!-- MAIN APP CONTAINER CONTAINER -->
  <main class="main-content">
    <!-- HEADER BAR BAR -->
    <header class="top-header">
      <div class="header-right">
        <div class="user-profile">
          <img src="<?php echo $avatarImg; ?>" alt="User Avatar" class="avatar global-user-avatar" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;">
          <div class="user-info">
            <span class="user-name global-user-name"><?php echo htmlspecialchars($adminName); ?></span>
            <span class="user-role">Store Owner</span>
          </div>
        </div>
      </div>
    </header>

    <!-- CONTENT BODY CONTAINER -->
    <div class="content-body">
      <div>
        <h1>Account Settings</h1>
        <p class="subtitle">Customize profile properties, layouts themes, and authentication values</p>
      </div>

      <div class="settings-container">
        <form id="admin-settings-form" enctype="multipart/form-data">
          
          <!-- FORM COMPONENT 1: RENDER AVATAR ACTIONS -->
          <div class="form-group">
            <label>Profile Image Avatar</label>
            <div class="avatar-preview-wrapper">
              <img src="<?php echo $avatarImg; ?>" id="avatar-preview-target" class="avatar-preview" alt="Avatar Preview">
              <div>
                <input type="file" id="settings-avatar-input" name="avatar_file" accept="image/*" style="display: none;" onchange="previewImageFile(this)">
                <button type="button" onclick="document.getElementById('settings-avatar-input').click();" style="background: #edf2f7; color: #4a5568; border: 1px solid #cbd5e0; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600;">
                  <i class="fa-solid fa-camera"></i> Upload New Photo
                </button>
              </div>
            </div>
          </div>

          <!-- FORM COMPONENT 2: PROFILE DISPLAY NAME -->
          <div class="form-group">
            <label for="settings-username">Profile User Name</label>
            <input type="text" id="settings-username" name="admin_name" class="form-control" value="<?php echo htmlspecialchars($adminName); ?>" required>
          </div>

          <!-- FORM COMPONENT 3: DISPLAY ACCENTS THEMES LIGHT vs DARK -->
          <div class="form-group">
            <label>Interface Preferences</label>
            <div class="toggle-wrapper">
              <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-moon" style="font-size: 18px; color: #805ad5;"></i>
                <div>
                  <div style="font-weight: 600; font-size: 14px;">Dark Theme Mode</div>
                  <div style="font-size: 12px; color: #718096;">Toggle high-contrast nighttime palette</div>
                </div>
              </div>
              <label class="switch">
                <input type="checkbox" id="settings-darkmode-toggle" name="dark_mode" value="1" onchange="toggleDarkThemeView(this.checked)">
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <!-- TRIGGER ACTION CONTROL BUTTONS -->
          <button type="submit" class="save-settings-btn">
            <i class="fa-solid fa-floppy-disk"></i> Save Configuration
          </button>
        </form>
      </div>
    </div>
  </main>

  <!-- CONTROL INTERACTION HANDLERS -->
  <script>
    // Load existing local user workspace layout values on first file parse executions
    document.addEventListener("DOMContentLoaded", function() {
        const localThemeSaved = localStorage.getItem("admin_dark_mode") === "true";
        document.getElementById("settings-darkmode-toggle").checked = localThemeSaved;
        toggleDarkThemeView(localThemeSaved);
    });

    // 1. Live Instant Dynamic Canvas Previewing Frameworks
    function previewImageFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview-target').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 2. Client side instant dark mode canvas toggler
    function toggleDarkThemeView(isDark) {
        if (isDark) {
            document.body.classList.add("dark-theme");
        } else {
            document.body.classList.remove("dark-theme");
        }
        localStorage.setItem("admin_dark_mode", isDark);
    }

    // 3. Form submission request dispatch handlers
    document.getElementById("admin-settings-form").addEventListener("submit", async function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch("settings_action.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                
                // Sync top header interface node layout elements locally right away without total page resets
                document.querySelector(".global-user-name").textContent = document.getElementById("settings-username").value;
                if(result.new_avatar) {
                    document.querySelector(".global-user-avatar").src = result.new_avatar;
                }
            } else {
                alert("Settings Update Failure: " + result.message);
            }
        } catch (error) {
            console.error("Transmission Error Matrix Exception: ", error);
            alert("Could not process payload instructions via backend routers API channels.");
        }
    });
  </script>
</body>
</html>
