<?php
require_once "config/koneksi.php";

$meja = isset($_GET['meja']) && !empty($_GET['meja']) ? htmlspecialchars($_GET['meja']) : null;

$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori_menu ORDER BY nama_kategori ASC");
$query_menu = mysqli_query($koneksi, "SELECT menu.*, kategori_menu.nama_kategori 
                                      FROM menu 
                                      JOIN kategori_menu ON menu.id_kategori = kategori_menu.id_kategori 
                                      ORDER BY menu.id_menu DESC");
$query_chips = mysqli_query($koneksi, "SELECT pertanyaan FROM chatbot_ai ORDER BY id_chatbot ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Warung Mogan Vegetarian – <?= $meja ? "Pemesanan Meja #".$meja : "Home" ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&display=swap" rel="stylesheet">
<style>
  :root {
    --ink: #1b2e2b; --ink-soft: #4a635d; --paper: #f4f7f6; --panel: #ffffff;
    --line: #d2e0db; --brand: #2d6a4f; --brand-dark: #1b4332; --accent: #f0a81c;
    --accent-btn: #52b788; --radius: 14px; --font: "Bricolage Grotesque", sans-serif;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body { font-family: var(--font); color: var(--ink); background: var(--paper); line-height: 1.6; overflow-x: hidden; <?= $meja ? 'padding-bottom: 80px;' : '' ?> }
  a { color: inherit; }
  
  .wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; width: 100%; }

  header { border-bottom: 1px solid var(--line); background: var(--paper); position: sticky; top: 0; z-index: 100; }
  header .wrap { display: flex; align-items: center; justify-content: space-between; min-height: 64px; }
  .logo { font-weight: 800; font-size: 1.25rem; text-decoration: none; color: var(--brand); }
  nav { display: flex; gap: 20px; }
  nav a { text-decoration: none; color: var(--ink-soft); font-weight: 600; font-size: 0.95rem; }
  .badge-meja { background: var(--brand); color: #fff; padding: 4px 14px; border-radius: 999px; font-size: 0.85rem; font-weight: 600; }

  .hero { padding: 32px 0 48px; }
  .hero .wrap { display: grid; grid-template-columns: 1.05fr 1fr; gap: 40px; align-items: start; }
  h1 { font-size: clamp(1.8rem, 4vw, 3rem); line-height: 1.15; font-weight: 800; }
  .hero p.lead { margin: 16px 0 24px; font-size: clamp(1rem, 2vw, 1.1rem); color: var(--ink-soft); }
  
  .btn-group { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 24px; }
  .btn { display: inline-block; padding: 10px 20px; border-radius: 999px; font-weight: 600; text-decoration: none; border: 2px solid var(--brand); font-size: 0.95rem; text-align: center; }
  .btn-primary { background: var(--brand); color: #fff; }
  .btn-ghost { color: var(--brand); }

  /* Input Pencarian Menu */
  .search-box { margin-bottom: 16px; }
  .search-input { width: 100%; padding: 10px 16px; border: 1.5px solid var(--line); border-radius: 999px; font-family: var(--font); font-size: 0.95rem; outline: none; }
  .search-input:focus { border-color: var(--brand); }

  .category-filter { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 16px; scrollbar-width: none; }
  .btn-cat { background: var(--panel); border: 1px solid var(--line); padding: 6px 14px; border-radius: 999px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
  .btn-cat.active, .btn-cat:hover { background: var(--brand); color: #fff; border-color: var(--brand); }

  .menu-grid { display: grid; gap: 12px; max-height: 350px; overflow-y: auto; padding-right: 4px; }
  .menu-card { background: var(--panel); border: 1px solid var(--line); padding: 12px 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
  .menu-info strong { font-size: 1rem; }
  .menu-info small { color: var(--ink-soft); font-weight: 600; display: block; }
  .menu-action { display: flex; align-items: center; gap: 12px; }
  .menu-price { font-weight: 800; color: var(--brand); white-space: nowrap; font-size: 0.95rem; }
  .btn-add { background: var(--brand); color: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }

  /* Chat Box */
  .chat { background: var(--panel); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; height: 480px; box-shadow: 0 18px 40px -22px rgba(27,46,43,.3); width: 100%; }
  .chat-head { background: var(--brand); color: #fff; padding: 14px 18px; display: flex; align-items: center; gap: 10px; }
  .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }
  #log { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; background: #f8fbfa; }
  .msg { max-width: 85%; padding: 10px 14px; border-radius: 16px; font-size: 0.92rem; white-space: pre-wrap; word-wrap: break-word; }
  .msg.bot { background: #e3efed; align-self: flex-start; }
  .msg.user { background: var(--brand); color: #fff; align-self: flex-end; }
  .chips { display: flex; gap: 8px; padding: 10px 16px 0; overflow-x: auto; border-top: 1px solid var(--line); background: #fff; scrollbar-width: none; }
  .chips button { font: inherit; font-size: .82rem; cursor: pointer; padding: 6px 12px; border-radius: 999px; border: 1.5px solid var(--brand); background: #fff; color: var(--brand); font-weight: 600; white-space: nowrap; }
  form { display: flex; gap: 8px; padding: 12px 16px 16px; background: #fff; }
  form input { flex: 1; font: inherit; font-size: 0.9rem; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 999px; }
  form button { font: inherit; font-weight: 600; font-size: 0.9rem; padding: 0 18px; border: 0; border-radius: 999px; background: var(--accent); color: var(--ink); cursor: pointer; }

  /* Sections */
  section.block { padding: 48px 0; border-top: 1px solid var(--line); }
  h2 { font-size: clamp(1.5rem, 3vw, 2.1rem); margin-bottom: 24px; }
  .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
  .card { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); padding: 20px; }
  .card h3 { font-size: 1.1rem; margin-bottom: 8px; color: var(--brand); }
  .card p { color: var(--ink-soft); font-size: 0.92rem; }
  .steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; counter-reset: s; list-style: none; padding: 0; }
  .steps li { counter-increment: s; border-left: 4px solid var(--accent); padding-left: 16px; }
  .steps li::before { content: "Langkah " counter(s); display: block; font-weight: 600; color: var(--brand); margin-bottom: 4px; }

  /* Floating Cart & Modals */
  .cart-bar { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); width: calc(100% - 40px); max-width: 600px; background: var(--brand); color: #fff; padding: 14px 20px; border-radius: 999px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 99; cursor: pointer; }
  .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 200; justify-content: center; align-items: center; padding: 20px; }
  .modal-content { background: #fff; border-radius: 20px; width: 100%; max-width: 480px; padding: 24px; max-height: 85vh; overflow-y: auto; }
  .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px; }
  .cart-item { margin-bottom: 12px; border-bottom: 1px dashed var(--line); padding-bottom: 8px; }
  .cart-item-row { display: flex; justify-content: space-between; align-items: center; }
  .qty-btn { width: 26px; height: 26px; border-radius: 50%; border: 1px solid var(--brand); background: #fff; color: var(--brand); font-weight: bold; cursor: pointer; }
  .cart-notes-input { width: 100%; margin-top: 6px; padding: 6px 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 0.85rem; font-family: var(--font); }
  .cart-summary { margin-top: 16px; border-top: 2px solid var(--line); padding-top: 12px; }
  .summary-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.95rem; }
  .btn-checkout { width: 100%; background: var(--brand); color: #fff; border: none; padding: 12px; border-radius: 999px; font-weight: bold; margin-top: 16px; cursor: pointer; font-size: 1rem; }

  footer { background: var(--ink); color: #c5d8d6; padding: 28px 0; font-size: 0.9rem; }
  footer .wrap { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; }

  @media (max-width: 860px) { .hero .wrap { grid-template-columns: 1fr; gap: 32px; } nav { display: none; } }
</style>
</head>
<body>

<header>
  <div class="wrap">
    <a class="logo" href="index.php">Warung Mogan</a>
    <?php if($meja): ?>
      <span class="badge-meja">Meja #<?= $meja ?></span>
    <?php else: ?>
      <nav aria-label="Navigasi utama">
        <a href="#layanan">Layanan AI</a>
        <a href="#cara-kerja">Cara Pemesanan</a>
        <a href="#kontak">Kontak & Lokasi</a>
      </nav>
    <?php endif; ?>
  </div>
</header>

<main>
  <section class="hero">
    <div class="wrap">
      <div>
        <?php if($meja): ?>
          <h1>Pesan Menu Meja #<?= $meja ?></h1>
          <p class="lead">Pilih menu favoritmu di bawah ini atau minta rekomendasi dari Chatbot AI.</p>

          <!-- Input Live Search Menu -->
          <div class="search-box">
            <input type="text" class="search-input" id="searchInput" placeholder="Cari nama makanan / minuman..." onkeyup="filterSearch()">
          </div>

          <div class="category-filter">
            <button class="btn-cat active" onclick="filterCategory('all', this)">Semua Kategori</button>
            <?php while($cat = mysqli_fetch_assoc($query_kategori)): ?>
              <button class="btn-cat" onclick="filterCategory('<?= strtolower(htmlspecialchars($cat['nama_kategori'])) ?>', this)">
                <?= htmlspecialchars($cat['nama_kategori']) ?>
              </button>
            <?php endwhile; ?>
          </div>

          <div class="menu-section">
            <div class="menu-grid" id="menuGrid">
              <?php if(mysqli_num_rows($query_menu) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query_menu)): ?>
                  <div class="menu-card" data-category="<?= strtolower(htmlspecialchars($row['nama_kategori'])) ?>" data-name="<?= strtolower(htmlspecialchars($row['nama_menu'])) ?>">
                    <div class="menu-info">
                      <strong><?= htmlspecialchars($row['nama_menu']) ?></strong>
                      <small><?= htmlspecialchars($row['nama_kategori']) ?></small>
                    </div>
                    <div class="menu-action">
                      <span class="menu-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                      <button class="btn-add" onclick="addToCart(<?= $row['id_menu'] ?>, '<?= htmlspecialchars($row['nama_menu']) ?>', <?=$row['harga'] ?>)">+</button>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php endif; ?>
            </div>
          </div>

        <?php else: ?>
          <h1>Tanya apa saja, dijawab langsung.</h1>
          <p class="lead">Asisten virtual kami siap membantu Anda mengecek layanan, harga, dan jam operasional kapan pun, tanpa antre.</p>
          
          <div class="btn-group">
            <a class="btn btn-primary" href="#layanan">Lihat layanan</a>
            <a class="btn btn-ghost" href="#cara-kerja">Cara kerja</a>
          </div>

          <div class="menu-section">
            <h3 style="margin-bottom: 12px; font-size: 1.05rem; color: var(--brand);">Daftar Menu Tersedia:</h3>
            <div class="menu-grid">
              <?php if(mysqli_num_rows($query_menu) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query_menu)): ?>
                  <div class="menu-card">
                    <div class="menu-info">
                      <strong><?= htmlspecialchars($row['nama_menu']) ?></strong>
                      <small><?= htmlspecialchars($row['nama_kategori']) ?></small>
                    </div>
                    <div class="menu-price">
                      Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Chatbot AI Area -->
      <div class="chat" role="region" aria-label="Asisten virtual">
        <div class="chat-head">
          <span class="dot"></span>
          <strong>Asisten Warung Mogan</strong>
          <small id="status">menghubungkan…</small>
        </div>
        <div id="log" aria-live="polite"></div>
        <div class="chips" id="chips">
          <?php if(mysqli_num_rows($query_chips) > 0): ?>
            <?php while($chip = mysqli_fetch_assoc($query_chips)): ?>
              <button type="button" onclick="send('<?= htmlspecialchars($chip['pertanyaan']) ?>')">
                <?= htmlspecialchars($chip['pertanyaan']) ?>
              </button>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>
        <form id="form" autocomplete="off">
          <input id="input" type="text" placeholder="Tulis pertanyaan Anda…" aria-label="Pesan" required>
          <button type="submit">Kirim</button>
        </form>
      </div>
    </div>
  </section>

  <section class="block" id="layanan">
    <div class="wrap">
      <h2>Layanan yang bisa kami bantu</h2>
      <div class="grid">
        <div class="card"><h3>Rekomendasi Menu</h3><p>Tanyakan rekomendasi makanan vegetarian favorit langsung kepada asisten virtual.</p></div>
        <div class="card"><h3>Pemesanan Mandiri</h3><p>Pilih dan tentukan pesanan tanpa harus menunggu pelayan datang membawa menu.</p></div>
        <div class="card"><h3>Informasi Makanan</h3><p>Cek daftar harga, bahan makanan, dan ketersediaan menu secara cepat.</p></div>
      </div>
    </div>
  </section>

  <section class="block" id="cara-kerja">
    <div class="wrap">
      <h2>Mulai dalam tiga langkah</h2>
      <ol class="steps">
        <li><h3>Scan QR Code</h3><p>Pindai kode QR yang tersedia di meja untuk membuka menu pemesanan.</p></li>
        <li><h3>Pilih / Tanya Chatbot</h3><p>Lihat daftar menu atau tanya rekomendasi pada asisten virtual.</p></li>
        <li><h3>Konfirmasi & Bayar</h3><p>Pesanan akan diproses oleh dapur dan pembayaran dilakukan langsung di kasir.</p></li>
      </ol>
    </div>
  </section>
</main>

<?php if($meja): ?>
  <div class="cart-bar" id="cartBar" style="display: none;" onclick="openModal()">
    <div>
      <span id="cartCount">0 Item</span> | <strong id="cartTotal">Rp 0</strong>
    </div>
    <strong>Lihat Pesanan &rarr;</strong>
  </div>

  <div class="modal" id="cartModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Pesanan Meja #<?= $meja ?></h3>
        <button onclick="closeModal()" style="border:none; background:none; font-size:1.5rem; cursor:pointer;">&times;</button>
      </div>
      <div id="cartItemsList"></div>
      <div class="cart-summary">
        <div class="summary-row"><span>Subtotal:</span><span id="summarySubtotal">Rp 0</span></div>
        <div class="summary-row"><span>PPN (10%):</span><span id="summaryPPN">Rp 0</span></div>
        <div class="summary-row" style="font-weight: bold; font-size:1.1rem; color:var(--brand);"><span>Total:</span><span id="summaryTotal">Rp 0</span></div>
      </div>
      <button class="btn-checkout" onclick="submitOrder()">Simpan Pesanan</button>
    </div>
  </div>
<?php endif; ?>

<footer id="kontak">
  <div class="wrap">
    <span>© 2026 Warung Mogan Vegetarian</span>
    <span>Mudutaki, Dalung, Badung, Bali · Buka Setiap Hari</span>
  </div>
</footer>

<script>
  const NO_MEJA = "<?= $meja ?>";
  let cart = [];
  let currentCategory = 'all';

  function filterCategory(cat, btn) {
    currentCategory = cat;
    document.querySelectorAll('.btn-cat').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterSearch();
  }

  // Live Search Menu
  function filterSearch() {
    const query = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase().trim() : '';
    const cards = document.querySelectorAll('.menu-card');

    cards.forEach(card => {
      const matchCat = (currentCategory === 'all' || card.getAttribute('data-category') === currentCategory);
      const matchName = card.getAttribute('data-name') ? card.getAttribute('data-name').includes(query) : true;

      if (matchCat && matchName) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function addToCart(id, nama, harga) {
    const index = cart.findIndex(item => item.id === id);
    if (index > -1) {
      cart[index].jumlah += 1;
    } else {
      cart.push({ id, nama, harga, jumlah: 1, catatan: '' });
    }
    updateCartUI();
  }

  function changeQty(id, delta) {
    const index = cart.findIndex(item => item.id === id);
    if (index > -1) {
      cart[index].jumlah += delta;
      if (cart[index].jumlah <= 0) cart.splice(index, 1);
    }
    updateCartUI();
  }

  function updateNotes(id, value) {
    const index = cart.findIndex(item => item.id === id);
    if (index > -1) {
      cart[index].catatan = value;
    }
  }

  function updateCartUI() {
    const cartBar = document.getElementById('cartBar');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    const cartItemsList = document.getElementById('cartItemsList');

    if(!cartBar) return;

    let totalQty = 0;
    let subtotal = 0;
    cartItemsList.innerHTML = '';

    cart.forEach(item => {
      totalQty += item.jumlah;
      subtotal += (item.harga * item.jumlah);

      cartItemsList.innerHTML += `
        <div class="cart-item">
          <div class="cart-item-row">
            <div>
              <strong>${item.nama}</strong><br>
              <small>Rp ${item.harga.toLocaleString('id-ID')} x ${item.jumlah}</small>
            </div>
            <div>
              <button class="qty-btn" onclick="changeQty(${item.id}, -1)">-</button>
              <span style="margin: 0 8px; font-weight:bold;">${item.jumlah}</span>
              <button class="qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
            </div>
          </div>
          <input type="text" class="cart-notes-input" placeholder="Catatan khusus (misal: pedas, es sedikit)..." value="${item.catatan}" onchange="updateNotes(${item.id}, this.value)">
        </div>
      `;
    });

    const ppn = subtotal * 0.10;
    const total = subtotal + ppn;

    if (totalQty > 0) {
      cartBar.style.display = 'flex';
      cartCount.textContent = `${totalQty} Item`;
      cartTotal.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    } else {
      cartBar.style.display = 'none';
      closeModal();
    }

    document.getElementById('summarySubtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
    document.getElementById('summaryPPN').textContent = `Rp ${ppn.toLocaleString('id-ID')}`;
    document.getElementById('summaryTotal').textContent = `Rp ${total.toLocaleString('id-ID')}`;
  }

  function openModal() { document.getElementById('cartModal').style.display = 'flex'; }
  function closeModal() { document.getElementById('cartModal').style.display = 'none'; }

  async function submitOrder() {
    if (cart.length === 0) return;

    try {
      const res = await fetch('simpan_pesanan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ meja: NO_MEJA, items: cart })
      });
      const data = await res.json();
      if (data.success) {
        alert(data.message);
        cart = [];
        updateCartUI();
      } else {
        alert('Gagal: ' + data.message);
      }
    } catch (e) {
      alert('Terjadi kesalahan koneksi.');
    }
  }

  // ====== Integrasi Chatbot RASA ======
  const RASA_URL = "http://localhost:5005/webhooks/rest/webhook";
  const SENDER_ID = "web-" + Math.random().toString(36).slice(2, 10);

  const log = document.getElementById("log");
  const form = document.getElementById("form");
  const input = document.getElementById("input");

  function addMessage(text, who) {
    const el = document.createElement("div");
    el.className = "msg " + who;
    el.textContent = text;
    log.appendChild(el);
    log.scrollTop = log.scrollHeight;
  }

  async function send(message) {
    addMessage(message, "user");
    const typing = addMessage("…", "bot");
    try {
      const res = await fetch(RASA_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sender: SENDER_ID, message })
      });
      const replies = await res.json();
      typing.remove();
      if (!replies.length) addMessage("Maaf, belum ada jawaban untuk itu.", "bot");
      replies.forEach(r => { if (r.text) addMessage(r.text, "bot"); });
    } catch (err) {
      typing.remove();
      addMessage("Server Chatbot RASA belum dinyalakan.", "bot");
    }
  }

  form.addEventListener("submit", e => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    input.value = "";
    send(text);
  });

  addMessage("Halo! Saya asisten virtual Warung Mogan Vegetarian 🌱. Ada yang bisa saya bantu?", "bot");
</script>
</body>
</html>