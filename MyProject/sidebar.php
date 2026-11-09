<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600&display=swap');

  .sidebar-container {
    width: 100%; /*  100%  col-lg-3  Bootstrap */
    background-color: #ffffff; 
    box-sizing: border-box;
  }

  .sidebar-menu {
    list-style: none;
    margin: 0;
    padding: 16px 12px;
    background-color: #ffffff; 
    font-family: 'Kantumruy Pro', sans-serif;
  }

  .sidebar-menu li {
    margin-bottom: 6px;
  }

  .sidebar-menu a, .menu-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 12px 16px;
    color: #475569; 
    text-decoration: none; 
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    background: none;
    border: none;
    cursor: pointer;
    box-sizing: border-box;
    transition: all 0.2s ease;
  }

  .sidebar-menu a:hover, .menu-btn:hover {
    background-color: #f1f5f9;
    color: #2563eb; 
  }

  .sidebar-menu a.active {
    background-color: #dbeafe;
    color: #1d4ed8;
    font-weight: 700;
    box-shadow: inset 3px 0 0 #2563eb;
  }

  .submenu {
    list-style: none;
    padding-left: 12px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
  }

  .submenu a {
    font-size: 14px;
    color: #64748b;
    padding: 8px 16px;
  }

  .arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
  }

  .dropdown.open .submenu {
    max-height: 200px;
    margin-top: 4px;
  }

  .dropdown.open .arrow {
    transform: rotate(180deg);
  }
</style>

<div class="sidebar-container">
  <?php $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? ''); ?>
  <ul class="sidebar-menu">
    <li><a href="home.php" class="<?= $currentPage === 'home.php' ? 'active' : '' ?>" <?= $currentPage === 'home.php' ? 'aria-current="page"' : '' ?>>Home</a></li>
    <li><a href="staff.php" class="<?= $currentPage === 'staff.php' ? 'active' : '' ?>" <?= $currentPage === 'staff.php' ? 'aria-current="page"' : '' ?>>Staff</a></li>
    <li><a href="product.php" class="<?= $currentPage === 'product.php' ? 'active' : '' ?>" <?= $currentPage === 'product.php' ? 'aria-current="page"' : '' ?>>Products</a></li>
    <!-- <li class="dropdown">
      <button class="menu-btn" onclick="toggleMenu(this)">
        <span>Products</span>
        <i class="fa-solid fa-chevron-down arrow"></i>
      </button>
      <ul class="submenu">
        <li><a href="staff.php">Product List</a></li>
        <li><a href="product.php">Add Product</a></li>
      </ul>
    </li>

    <li class="dropdown">
      <button class="menu-btn" onclick="toggleMenu(this)">
        <span>Product Categories</span>
        <i class="fa-solid fa-chevron-down arrow"></i>
      </button>
      <ul class="submenu">
        <li><a href="shirts.php">Shirts</a></li>
        <li><a href="trousers.php">Trousers</a></li>
      </ul>
    </li> -->
  </ul>
</div>

<script>
  function toggleMenu(element) {
    const parent = element.parentElement;
    parent.classList.toggle('open');
  }
</script>
