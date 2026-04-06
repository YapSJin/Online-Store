<!-- public/header.php -->
<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 

// 统一用“站点根目录绝对路径”生成链接，避免不同目录深度导致 CSS/图片/跳转 404
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
$knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
$appRoot = '';
if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
    $appRoot = '/' . $parts[0];
}

$publicUrl = $appRoot . '/public';
$adminUrl = $appRoot . '/admin';
$assetsUrl = $appRoot . '/assets';

$sessionUser = $_SESSION['user'] ?? null;
$sessionUserRole = null;
$sessionUserName = '';
if (is_array($sessionUser)) {
    $sessionUserRole = $sessionUser['role'] ?? null;
    $sessionUserName = (string)($sessionUser['username'] ?? '');
} elseif (is_object($sessionUser)) {
    $sessionUserRole = $sessionUser->role ?? null;
    $sessionUserName = (string)($sessionUser->username ?? '');
}

$admin_href = $adminUrl . '/manageUser.php';
$products_href = ($sessionUserRole === 'admin') ? ($publicUrl . '/admin/index.php') : ($publicUrl . '/product.php');
$members_href = $publicUrl . '/member/index.php';
$home_href = $publicUrl . '/home.php';
$login_href = $publicUrl . '/login.php';
$register_href = $publicUrl . '/register.php';
$logout_href = $publicUrl . '/logout.php';
$cart_href = $publicUrl . '/cart.php';

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $cartCount += (int)$item['quantity'];
        }
    }
}

$faviconFile = realpath(__DIR__ . '/../assets/image/home/logo.png');
$faviconVersion = $faviconFile && file_exists($faviconFile) ? filemtime($faviconFile) : time();
$faviconUrl = $assetsUrl . '/image/home/logo.png?v=' . $faviconVersion;

$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
$showAiBot = true;
if (strpos($scriptPath, '/admin/') !== false) $showAiBot = false;
if (strpos($scriptPath, '/public/admin/') !== false) $showAiBot = false;
if (preg_match('#/public/(login|register|admin_login)\.php$#', $scriptPath)) $showAiBot = false;
?>

<link rel="icon" type="image/png" href="<?php echo $faviconUrl; ?>">
<script>
(function () {
    var href = "<?php echo $faviconUrl; ?>";
    try {
        var doc = document;
        var head = doc.head || doc.getElementsByTagName('head')[0];
        if (!head) {
            head = doc.createElement('head');
            if (doc.documentElement && doc.documentElement.firstChild) {
                doc.documentElement.insertBefore(head, doc.documentElement.firstChild);
            } else if (doc.documentElement) {
                doc.documentElement.appendChild(head);
            }
        }

        var setLink = function (rel) {
            var link = doc.querySelector("link[rel='" + rel + "']");
            if (!link) {
                link = doc.createElement('link');
                link.rel = rel;
                head.appendChild(link);
            }
            link.type = 'image/png';
            link.href = href;
        };

        setLink('icon');
        setLink('shortcut icon');

        var apple = doc.querySelector("link[rel='apple-touch-icon']");
        if (!apple) {
            apple = doc.createElement('link');
            apple.rel = 'apple-touch-icon';
            head.appendChild(apple);
        }
        apple.href = href;
    } catch (e) {}
})();
</script>
<link rel="stylesheet" href="<?php echo $assetsUrl; ?>/css/header.css">
<link rel="stylesheet" href="<?php echo $assetsUrl; ?>/css/footer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<?php if ($showAiBot): ?>
<link rel="stylesheet" href="<?php echo $assetsUrl; ?>/css/chatbot.css">
<?php endif; ?>

<header>
    <a href="<?php echo $home_href; ?>" class="logo-link">
        <img src="<?php echo $assetsUrl; ?>/image/home/logo.png" class="logo">
        <span class="brand-name">LALA</span>
    </a>

    <nav class="nav-desktop">
        <div class="nav-links">
            <a href="<?php echo $home_href; ?>">Home</a>
            <a href="<?php echo $products_href; ?>">Products</a>
            <?php if($sessionUserRole === 'admin'): ?>
                <a href="<?php echo $members_href; ?>">Members</a>
            <?php endif; ?>
            <a href="<?php echo $home_href; ?>#contact">Contact</a>
            <?php if ($sessionUserRole !== 'admin'): ?>
                <a href="<?php echo $cart_href; ?>" style="position: relative; display: inline-block;">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <?php if ($cartCount > 0): ?>
                        <span style="position:absolute; top:-8px; right:-10px; background:red; color:white; border-radius:999px; padding:2px 6px; font-size:12px; line-height:1;">
                            <?php echo $cartCount; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if($sessionUserRole === 'admin'): ?>
                <a href="<?php echo $admin_href; ?>" class="admin-panel-link"><i class="fa-solid fa-user-shield"></i> Admin Panel</a>
            <?php endif; ?>
        </div>

        <div class="auth-buttons">
            <?php if($sessionUser): ?>
                <span class="user-greeting">
                    <a href="<?php echo $publicUrl; ?>/profile.php" class="user-name-link">
                        <?php echo htmlspecialchars($sessionUserName !== '' ? $sessionUserName : 'User'); ?>
                    </a>
                </span>
                <a href="<?php echo $logout_href; ?>" class="login-btn">Logout</a>
            <?php else: ?>
                <a href="<?php echo $login_href; ?>" class="login-btn">Login</a>
                <a href="<?php echo $register_href; ?>" class="register-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php if ($showAiBot): ?>
<div class="ai-bot-float" id="aiBotFloat" aria-label="AI Bot">
    <img class="ai-bot-logo" src="<?php echo $publicUrl; ?>/member/uploads/ai bot.webp" alt="AI">
</div>

<div class="ai-bot-panel" id="aiBotPanel">
    <div class="ai-bot-header">
        <div class="ai-bot-title">LALA AI Assistant</div>
        <div class="ai-bot-close" id="aiBotClose">✕</div>
    </div>
    <div class="ai-bot-quick" id="aiBotQuick"></div>
    <div class="ai-bot-messages" id="aiBotMessages"></div>
    <div class="ai-bot-input">
        <textarea class="ai-bot-textarea" id="aiBotInput" placeholder="Ask about products, price, stock, checkout..."></textarea>
        <div class="ai-bot-send" id="aiBotSend">Send</div>
    </div>
</div>

<script>
(function () {
    var floatEl = document.getElementById('aiBotFloat');
    var panelEl = document.getElementById('aiBotPanel');
    var closeEl = document.getElementById('aiBotClose');
    var msgEl = document.getElementById('aiBotMessages');
    var quickEl = document.getElementById('aiBotQuick');
    var inputEl = document.getElementById('aiBotInput');
    var sendEl = document.getElementById('aiBotSend');
    if (!floatEl || !panelEl || !closeEl || !msgEl || !quickEl || !inputEl || !sendEl) return;

    var apiUrl = "<?php echo $publicUrl; ?>/api/chatbot.php";

    var suggestions = [
        { label: 'Latest Products', text: 'Show latest products' },
        { label: 'Hoodies', text: 'Show hoodies' },
        { label: 'Pants', text: 'Show pants' },
        { label: 'Shirts', text: 'Show shirts' },
        { label: 'Checkout', text: 'How to checkout' },
        { label: 'My Orders', text: 'Where is my orders page' },
        { label: 'Contact', text: 'Contact' }
    ];

    function clamp(v, min, max) { return Math.min(Math.max(v, min), max); }

    function addMessage(text, who) {
        var row = document.createElement('div');
        row.className = 'ai-msg ' + who;
        var bubble = document.createElement('div');
        bubble.className = 'ai-bubble';
        bubble.textContent = text;
        row.appendChild(bubble);
        msgEl.appendChild(row);
        msgEl.scrollTop = msgEl.scrollHeight;
    }

    function addUser(text) { addMessage(text, 'user'); }
    function addBot(text) { addMessage(text, 'bot'); }

    function setSending(isSending) {
        if (isSending) sendEl.classList.add('disabled');
        else sendEl.classList.remove('disabled');
    }

    async function ask(text) {
        if (!text) return;
        if (sendEl.classList.contains('disabled')) return;
        addUser(text);
        setSending(true);
        try {
            var res = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            var data = await res.json();
            addBot((data && data.reply) ? data.reply : 'Sorry, something went wrong.');
        } catch (e) {
            addBot('Sorry, I cannot connect right now.');
        } finally {
            setSending(false);
        }
    }

    function openPanel() {
        if (panelEl.classList.contains('open')) return;
        panelEl.classList.add('open');
        positionPanel();
        if (msgEl.childElementCount === 0) {
            addBot("Hi! I can answer product details, price, stock, and checkout.\nTry: \"Product id 3\" or click a product card.");
        }
        setTimeout(function () { inputEl.focus(); }, 50);
    }

    function closePanel() {
        panelEl.classList.remove('open');
    }

    function togglePanel() {
        if (panelEl.classList.contains('open')) closePanel();
        else openPanel();
    }

    function positionPanel() {
        var rect = floatEl.getBoundingClientRect();
        var left = rect.left;
        var top = rect.top;
        panelEl.style.left = clamp(left - 302, 8, window.innerWidth - panelEl.offsetWidth - 8) + 'px';
        panelEl.style.top = clamp(top - 490, 8, window.innerHeight - panelEl.offsetHeight - 8) + 'px';
        panelEl.style.right = 'auto';
        panelEl.style.bottom = 'auto';
    }

    function renderSuggestions() {
        quickEl.innerHTML = '';
        suggestions.forEach(function (s) {
            var chip = document.createElement('div');
            chip.className = 'ai-chip';
            chip.textContent = s.label;
            chip.addEventListener('click', function () {
                openPanel();
                ask(s.text);
            });
            quickEl.appendChild(chip);
        });
    }

    renderSuggestions();

    var storageKey = 'lala_ai_bot_pos_v2';
    var pos = null;
    try { pos = JSON.parse(localStorage.getItem(storageKey) || 'null'); } catch (e) { pos = null; }
    if (pos && typeof pos.left === 'number' && typeof pos.top === 'number') {
        floatEl.style.left = pos.left + 'px';
        floatEl.style.top = pos.top + 'px';
        floatEl.style.right = 'auto';
        floatEl.style.bottom = 'auto';
    }

    var dragging = false;
    var startX = 0, startY = 0, startLeft = 0, startTop = 0;
    var moved = false;

    floatEl.addEventListener('pointerdown', function (e) {
        dragging = true;
        moved = false;
        floatEl.setPointerCapture(e.pointerId);
        var rect = floatEl.getBoundingClientRect();
        startX = e.clientX;
        startY = e.clientY;
        startLeft = rect.left;
        startTop = rect.top;
    });

    floatEl.addEventListener('pointermove', function (e) {
        if (!dragging) return;
        var dx = e.clientX - startX;
        var dy = e.clientY - startY;
        if (Math.abs(dx) + Math.abs(dy) > 5) moved = true;
        var left = clamp(startLeft + dx, 8, window.innerWidth - floatEl.offsetWidth - 8);
        var top = clamp(startTop + dy, 8, window.innerHeight - floatEl.offsetHeight - 8);
        floatEl.style.left = left + 'px';
        floatEl.style.top = top + 'px';
        floatEl.style.right = 'auto';
        floatEl.style.bottom = 'auto';
        if (panelEl.classList.contains('open')) positionPanel();
    });

    floatEl.addEventListener('pointerup', function (e) {
        if (!dragging) return;
        dragging = false;
        try {
            var rect = floatEl.getBoundingClientRect();
            localStorage.setItem(storageKey, JSON.stringify({ left: rect.left, top: rect.top }));
        } catch (err) {}
        if (!moved) togglePanel();
    });

    closeEl.addEventListener('click', closePanel);
    sendEl.addEventListener('click', function () {
        var text = (inputEl.value || '').trim();
        if (!text) return;
        inputEl.value = '';
        ask(text);
    });

    inputEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            var text = (inputEl.value || '').trim();
            if (!text) return;
            inputEl.value = '';
            ask(text);
        }
    });

    document.addEventListener('click', function (e) {
        var card = e.target && e.target.closest ? e.target.closest('[data-chatbot-product-id]') : null;
        if (!card) return;
        if (e.target && e.target.closest && e.target.closest('.btn-group')) return;
        var pid = card.getAttribute('data-chatbot-product-id');
        var pname = card.getAttribute('data-chatbot-product-name') || '';
        if (!pid) return;
        openPanel();
        ask('Product id ' + pid + (pname ? (' (' + pname + ')') : ''));
    });

    window.lalaAiBot = {
        open: openPanel,
        close: closePanel,
        ask: function (text) { openPanel(); ask(text); },
        askProduct: function (id) { openPanel(); ask('Product id ' + id); }
    };
})();
</script>
<?php endif; ?>
