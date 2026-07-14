<?php
include "connexion.php";
include "params.php";

// Catalogue : produits actifs, Femme d'abord puis Homme, dans l'ordre defini
$produits = [];
$res = mysqli_query($conn, "SELECT * FROM produits WHERE actif = 1 ORDER BY FIELD(genre,'Femme','Homme'), position, id");
if ($res) { while ($r = mysqli_fetch_assoc($res)) $produits[] = $r; }

// Best-sellers (produits marques, tries par rang)
$bestsellers = [];
$rb = mysqli_query($conn, "SELECT * FROM produits WHERE actif = 1 AND est_bestseller = 1 ORDER BY bestseller_rang, id");
if ($rb) { while ($r = mysqli_fetch_assoc($rb)) $bestsellers[] = $r; }

// Categories actives
$categories = [];
$rc = mysqli_query($conn, "SELECT * FROM categories WHERE actif = 1 ORDER BY position, id");
if ($rc) { while ($r = mysqli_fetch_assoc($rc)) $categories[] = $r; }

// Donnees pour les modales (JS)
$pdata = array_map(function ($p) {
    return [
        'name'     => $p['nom'],
        'category' => $p['categorie'],
        'price'    => $p['prix'],
        'image'    => $p['image'],
        'desc'     => $p['description'],
        'notes'    => array_values(array_filter(array_map('trim', explode(',', (string)$p['notes_liste'])))),
        'reviews'  => $p['avis'],
    ];
}, $produits);

// Etoiles a partir d'une note sur 5
function sheyluna_stars($note) {
    $note = (float) $note; $h = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($note >= $i)           $h .= '<i class="fas fa-star"></i>';
        elseif ($note >= $i - 0.5) $h .= '<i class="fas fa-star-half-alt"></i>';
        else                       $h .= '<i class="far fa-star"></i>';
    }
    return $h;
}

// Carte produit ($index = position globale, pour openModal)
function sheyluna_card($p, $index) {
    $nom   = htmlspecialchars($p['nom']);
    $nomJs = htmlspecialchars(json_encode($p['nom'], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
    ob_start(); ?>
            <div class="arrival-card animate-on-scroll" onclick="openModal(<?php echo $index; ?>)">
                <?php if (!empty($p['badge'])): ?><span class="arrival-card-badge <?php echo $p['badge_type']==='hot'?'hot':'new'; ?>"><?php echo htmlspecialchars($p['badge']); ?></span><?php endif; ?>
                <button class="arrival-card-wishlist" onclick="event.stopPropagation(); toggleWishlist(this)"><i class="far fa-heart"></i></button>
                <div class="arrival-card-image">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo $nom; ?>">
                    <div class="arrival-card-overlay">
                        <button class="overlay-btn" onclick="event.stopPropagation(); addToCart(<?php echo $nomJs; ?>)"><i class="fas fa-shopping-bag"></i></button>
                        <button class="overlay-btn" onclick="event.stopPropagation(); openModal(<?php echo $index; ?>)"><i class="fas fa-eye"></i></button>
                        <button class="overlay-btn"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="arrival-card-body">
                    <div class="arrival-card-category"><?php echo htmlspecialchars($p['categorie']); ?></div>
                    <h3 class="arrival-card-name"><?php echo $nom; ?></h3>
                    <p class="arrival-card-notes"><?php echo htmlspecialchars((string)$p['note_courte']); ?></p>
                    <div class="arrival-card-rating">
                        <div class="stars"><?php echo sheyluna_stars($p['note_etoiles']); ?></div>
                        <span class="count">(<?php echo htmlspecialchars((string)$p['avis']); ?>)</span>
                    </div>
                    <div class="arrival-card-footer">
                        <div class="arrival-card-price">
                            <span class="current"><?php echo htmlspecialchars($p['prix']); ?></span>
                            <?php if (!empty($p['prix_original'])): ?><span class="original"><?php echo htmlspecialchars($p['prix_original']); ?></span><?php endif; ?>
                        </div>
                        <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart(<?php echo $nomJs; ?>)">Ajouter</button>
                    </div>
                </div>
            </div>
    <?php return ob_get_clean();
}

// Element de la sidebar (menu lateral)
function sheyluna_sidebar_item($p, $index) {
    $nom = htmlspecialchars($p['nom']);
    ob_start(); ?>
            <div class="sidebar-perfume-item" onclick="openModal(<?php echo $index; ?>)">
                <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo $nom; ?>" class="sidebar-perfume-thumb">
                <div class="sidebar-perfume-info">
                    <h4><?php echo $nom; ?></h4>
                    <span><?php echo htmlspecialchars($p['prix']); ?></span>
                </div>
            </div>
    <?php return ob_get_clean();
}

// Carte best-seller ($rang = numero affiche)
function sheyluna_bestseller_card($p, $rang) {
    $nom = htmlspecialchars($p['nom']);
    ob_start(); ?>
            <div class="bestseller-card animate-on-scroll">
                <div class="bestseller-rank"><?php echo $rang; ?></div>
                <div class="bestseller-image">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo $nom; ?>">
                </div>
                <div class="bestseller-body">
                    <h3 class="bestseller-name"><?php echo $nom; ?></h3>
                    <div class="bestseller-price"><?php echo htmlspecialchars($p['prix']); ?></div>
                    <div class="bestseller-stars">
                        <?php echo sheyluna_stars($p['note_etoiles']); ?>
                        <span style="color: var(--medium-gray); font-size: 0.8rem; margin-left: 5px;"><?php echo htmlspecialchars((string)$p['avis']); ?></span>
                    </div>
                </div>
            </div>
    <?php return ob_get_clean();
}

// Carte categorie
function sheyluna_category_card($c) {
    $titre = htmlspecialchars($c['titre']);
    ob_start(); ?>
            <div class="category-card animate-on-scroll">
                <img src="<?php echo htmlspecialchars($c['image']); ?>" alt="<?php echo $titre; ?>" class="category-card-bg">
                <div class="category-card-overlay">
                    <h3 class="category-card-title"><?php echo $titre; ?></h3>
                    <span class="category-card-count"><?php echo htmlspecialchars((string)$c['nombre_texte']); ?></span>
                </div>
            </div>
    <?php return ob_get_clean();
}
?>

                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <style>
                body {
                  background-color: white; /* Ensure the iframe has a white background */
                }

                /* ============================================
   SHEYLUNA PARFUMS - STYLES PRINCIPAUX
   ============================================ */

@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Montserrat:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&display=swap');

:root {
    --gold-primary: #C9A96E;
    --gold-light: #E8D5B0;
    --gold-dark: #A68B5B;
    --gold-shimmer: #D4AF37;
    --pink-soft: #F5E6F0;
    --pink-medium: #E8B4D9;
    --pink-dark: #D489C7;
    --rose-blush: #FADADD;
    --cream: #FFF8F0;
    --cream-light: #FFFDF9;
    --white: #FFFFFF;
    --black: #1A1A1A;
    --dark-gray: #2D2D2D;
    --medium-gray: #6B6B6B;
    --light-gray: #E8E8E8;
    --star-gold: #FFD700;
    --shadow-soft: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-medium: 0 8px 40px rgba(0,0,0,0.12);
    --shadow-strong: 0 15px 60px rgba(0,0,0,0.15);
    --transition-smooth: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    overflow-x: hidden;
}
.cart-drawer {
  position: fixed;
  top: 0;
  right: -400px;
  width: 360px;
  height: 100vh;
  background: white;
  z-index: 99999;
  padding: 25px;
  box-shadow: -5px 0 20px rgba(0,0,0,0.2);
  transition: 0.3s;
}

.cart-drawer.active {
  right: 0;
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-header button {
  font-size: 28px;
  border: none;
  background: #f5e6f0;
  border-radius: 50%;
  width: 40px;
  height: 40px;
}

.cart-item {
  margin: 20px 0;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.cart-item-info h3 { margin: 0 0 4px; font-size: 1rem; }
.cart-item-info p { margin: 0; font-size: 0.85rem; color: #888; }

.cart-item-remove {
  border: none;
  background: #f8d7da;
  color: #c62828;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.2s;
}
.cart-item-remove:hover { background: #f1a9b0; }

.cart-empty { text-align: center; color: #999; font-style: italic; padding: 30px 0; }

.checkout-btn {
  width: 100%;
  padding: 12px;
  background: #c9a96e;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
}

.clear-cart-btn {
  width: 100%;
  padding: 10px;
  margin-top: 10px;
  background: transparent;
  color: #c62828;
  border: 1px solid #f1c0c0;
  border-radius: 25px;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.2s;
}
.clear-cart-btn:hover { background: #fdecec; }
body {
    font-family: 'Montserrat', sans-serif;
    background-color: var(--cream-light);
    color: var(--dark-gray);
    line-height: 1.6;
    overflow-x: hidden;
}

/* ============================================
   PRELOADER
   ============================================ */
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--cream-light) 0%, var(--pink-soft) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transition: opacity 0.8s ease, visibility 0.8s ease;
}

.preloader.hidden {
    opacity: 0;
    visibility: hidden;
}

.preloader-logo {
    width: 180px;
    animation: preloaderPulse 1.5s ease-in-out infinite;
}

.preloader-text {
    font-family: 'Playfair Display', serif;
    color: var(--gold-primary);
    font-size: 1.5rem;
    margin-top: 20px;
    letter-spacing: 4px;
    animation: preloaderFade 1.5s ease-in-out infinite;
}

.preloader-bar {
    width: 200px;
    height: 2px;
    background: var(--light-gray);
    margin-top: 30px;
    border-radius: 2px;
    overflow: hidden;
}

.preloader-bar-inner {
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, var(--gold-primary), var(--gold-shimmer), var(--gold-primary));
    border-radius: 2px;
    animation: preloaderProgress 2s ease-in-out forwards;
}

@keyframes preloaderPulse {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.05); opacity: 1; }
}

@keyframes preloaderFade {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

@keyframes preloaderProgress {
    0% { width: 0%; }
    100% { width: 100%; }
}

/* ============================================
   SIDEBAR NAVIGATION
   ============================================ */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 9998;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition-smooth);
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

.sidebar {
    position: fixed;
    top: 0;
    left: -380px;
    width: 360px;
    max-width: 85vw;
    height: 100%;
    background: linear-gradient(180deg, var(--white) 0%, var(--cream) 100%);
    z-index: 9999;
    transition: left 0.5s cubic-bezier(0.77, 0, 0.175, 1);
    overflow-y: auto;
    box-shadow: var(--shadow-strong);
}

.sidebar.active {
    left: 0;
}

.sidebar-header {
    padding: 30px 25px;
    background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-dark) 100%);
    text-align: center;
    position: relative;
}

.sidebar-logo {
    width: 120px;
    filter: brightness(10);
    margin-bottom: 10px;
}

.sidebar-title {
    font-family: 'Playfair Display', serif;
    color: var(--white);
    font-size: 1.6rem;
    letter-spacing: 3px;
    font-weight: 500;
}

.sidebar-subtitle {
    color: rgba(255,255,255,0.8);
    font-size: 0.7rem;
    letter-spacing: 5px;
    text-transform: uppercase;
    margin-top: 5px;
}

.sidebar-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-smooth);
}

.sidebar-close:hover {
    background: rgba(255,255,255,0.4);
    transform: rotate(90deg);
}

.sidebar-nav {
    padding: 20px 0;
}

.sidebar-section-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--medium-gray);
    padding: 15px 30px 10px;
    font-weight: 600;
}

.sidebar-link {
    display: flex;
    align-items: center;
    padding: 14px 30px;
    color: var(--dark-gray);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 400;
    transition: var(--transition-smooth);
    border-left: 3px solid transparent;
    position: relative;
}

.sidebar-link:hover {
    background: var(--pink-soft);
    color: var(--gold-dark);
    border-left-color: var(--gold-primary);
    padding-left: 38px;
}

.sidebar-link i {
    width: 24px;
    margin-right: 15px;
    font-size: 1rem;
    color: var(--gold-primary);
}

.sidebar-link .badge {
    position: absolute;
    right: 25px;
    background: linear-gradient(135deg, var(--pink-dark), var(--pink-medium));
    color: white;
    font-size: 0.65rem;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 1px;
}

.sidebar-divider {
    height: 1px;
    background: var(--light-gray);
    margin: 10px 25px;
}

.sidebar-perfume-item {
    display: flex;
    align-items: center;
    padding: 12px 30px;
    transition: var(--transition-smooth);
    cursor: pointer;
}

.sidebar-perfume-item:hover {
    background: var(--pink-soft);
}

.sidebar-perfume-thumb {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    object-fit: cover;
    margin-right: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.sidebar-perfume-info h4 {
    font-family: 'Playfair Display', serif;
    font-size: 0.9rem;
    color: var(--dark-gray);
    margin-bottom: 2px;
}

.sidebar-perfume-info span {
    font-size: 0.8rem;
    color: var(--gold-primary);
    font-weight: 600;
}

.sidebar-footer {
    padding: 25px 30px;
    border-top: 1px solid var(--light-gray);
    margin-top: 10px;
}

.sidebar-social {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 15px;
}

.sidebar-social a {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: white;
    transition: var(--transition-smooth);
    text-decoration: none;
}

.sidebar-social a:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.sidebar-social .whatsapp { background: #25D366; }
.sidebar-social .facebook { background: #1877F2; }
.sidebar-social .instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }

.sidebar-footer-text {
    text-align: center;
    font-size: 0.7rem;
    color: var(--medium-gray);
    letter-spacing: 1px;
}

/* ============================================
   HAMBURGER BUTTON
   ============================================ */
.hamburger-btn {
    position: fixed;
    top: 25px;
    left: 25px;
    z-index: 9997;
    background: var(--white);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-smooth);
}

.hamburger-btn:hover {
    box-shadow: var(--shadow-medium);
    transform: scale(1.05);
}

.hamburger-btn span {
    display: block;
    width: 22px;
    height: 2px;
    background: var(--gold-dark);
    border-radius: 2px;
    transition: var(--transition-smooth);
}

.hamburger-btn.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.hamburger-btn.active span:nth-child(2) {
    opacity: 0;
}

.hamburger-btn.active span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -5px);
}

/* ============================================
   TOP HEADER BAR
   ============================================ */
.top-bar {
    background: linear-gradient(135deg, var(--dark-gray) 0%, var(--black) 100%);
    color: var(--gold-light);
    text-align: center;
    padding: 10px 20px;
    font-size: 0.8rem;
    letter-spacing: 2px;
    position: relative;
    z-index: 100;
}

.top-bar span {
    color: var(--gold-shimmer);
    font-weight: 600;
}

.top-bar i {
    margin-right: 8px;
    color: var(--pink-medium);
}

/* ============================================
   MAIN NAVIGATION BAR
   ============================================ */
.main-nav {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(201, 169, 110, 0.15);
    transition: var(--transition-smooth);
}

.main-nav.scrolled {
    box-shadow: 0 2px 30px rgba(0,0,0,0.08);
}

.nav-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 75px;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-left: 60px;
}

.nav-logo {
    height: 45px;
    transition: var(--transition-smooth);
}

.nav-logo:hover {
    transform: scale(1.05);
}

.nav-center {
    display: flex;
    gap: 30px;
}

.nav-link {
    text-decoration: none;
    color: var(--dark-gray);
    font-size: 0.82rem;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    position: relative;
    padding: 5px 0;
    transition: var(--transition-smooth);
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--gold-primary), var(--gold-shimmer));
    transition: width 0.4s ease;
}

.nav-link:hover {
    color: var(--gold-primary);
}

.nav-link:hover::after {
    width: 100%;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--dark-gray);
    font-size: 1.1rem;
    cursor: pointer;
    transition: var(--transition-smooth);
    position: relative;
    background: transparent;
    border: none;
}

.nav-icon:hover {
    background: var(--pink-soft);
    color: var(--gold-primary);
}

.nav-icon .cart-count {
    position: absolute;
    top: -2px;
    right: -2px;
    background: linear-gradient(135deg, var(--pink-dark), var(--pink-medium));
    color: white;
    font-size: 0.6rem;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

/* ============================================
   HERO SECTION
   ============================================ */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: linear-gradient(135deg, var(--cream-light) 0%, var(--pink-soft) 50%, var(--cream) 100%);
}

.hero-bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.03;
    background-image: radial-gradient(circle at 20% 50%, var(--gold-primary) 1px, transparent 1px),
                      radial-gradient(circle at 80% 20%, var(--gold-primary) 1px, transparent 1px),
                      radial-gradient(circle at 60% 80%, var(--gold-primary) 1px, transparent 1px);
    background-size: 60px 60px, 80px 80px, 70px 70px;
}

.hero-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 500px;
    opacity: 0.06;
    pointer-events: none;
    animation: watermarkFloat 8s ease-in-out infinite;
}

@keyframes watermarkFloat {
    0%, 100% { transform: translate(-50%, -50%) scale(1); }
    50% { transform: translate(-50%, -52%) scale(1.03); }
}

.hero-content {
    position: relative;
    z-index: 10;
    text-align: center;
    max-width: 900px;
    padding: 0 30px;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(201, 169, 110, 0.1);
    border: 1px solid rgba(201, 169, 110, 0.3);
    padding: 8px 25px;
    border-radius: 50px;
    font-size: 0.75rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--gold-dark);
    margin-bottom: 30px;
    animation: fadeInDown 1s ease 0.3s both;
}

.hero-badge i {
    color: var(--pink-dark);
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 5rem);
    color: var(--dark-gray);
    line-height: 1.15;
    margin-bottom: 25px;
    animation: fadeInUp 1s ease 0.5s both;
}

.hero-title .highlight {
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer), var(--gold-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    color: var(--medium-gray);
    font-weight: 300;
    font-style: italic;
    margin-bottom: 40px;
    animation: fadeInUp 1s ease 0.7s both;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease 0.9s both;
}

.btn-primary {
    background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-shimmer) 50%, var(--gold-dark) 100%);
    color: white;
    border: none;
    padding: 16px 45px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition-smooth);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s ease;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(201, 169, 110, 0.4);
}

.btn-secondary {
    background: transparent;
    color: var(--dark-gray);
    border: 2px solid var(--gold-primary);
    padding: 14px 45px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition-smooth);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-secondary:hover {
    background: var(--gold-primary);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(201, 169, 110, 0.3);
}

.hero-scroll {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: var(--medium-gray);
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    animation: fadeInUp 1s ease 1.2s both;
}

.hero-scroll-line {
    width: 1px;
    height: 50px;
    background: linear-gradient(to bottom, var(--gold-primary), transparent);
    animation: scrollPulse 2s ease-in-out infinite;
}

@keyframes scrollPulse {
    0%, 100% { opacity: 0.3; height: 50px; }
    50% { opacity: 1; height: 60px; }
}

.hero-floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
}

.floating-element {
    position: absolute;
    opacity: 0.15;
    animation: floatRandom 10s ease-in-out infinite;
}

.floating-element:nth-child(1) { top: 15%; left: 10%; animation-delay: 0s; font-size: 3rem; }
.floating-element:nth-child(2) { top: 25%; right: 12%; animation-delay: 2s; font-size: 2rem; }
.floating-element:nth-child(3) { bottom: 20%; left: 15%; animation-delay: 4s; font-size: 2.5rem; }
.floating-element:nth-child(4) { bottom: 30%; right: 8%; animation-delay: 6s; font-size: 1.8rem; }
.floating-element:nth-child(5) { top: 50%; left: 5%; animation-delay: 1s; font-size: 2.2rem; }

@keyframes floatRandom {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(10px, -20px) rotate(5deg); }
    50% { transform: translate(-5px, 10px) rotate(-3deg); }
    75% { transform: translate(15px, 5px) rotate(4deg); }
}

/* ============================================
   SECTION STYLES
   ============================================ */
.section {
    padding: 100px 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--gold-primary);
    font-weight: 600;
    margin-bottom: 15px;
}

.section-tag::before,
.section-tag::after {
    content: '';
    width: 30px;
    height: 1px;
    background: var(--gold-primary);
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 4vw, 3rem);
    color: var(--dark-gray);
    margin-bottom: 15px;
    line-height: 1.2;
}

.section-desc {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    color: var(--medium-gray);
    font-style: italic;
    max-width: 600px;
    margin: 0 auto;
}

/* ============================================
   NEW ARRIVALS
   ============================================ */
.new-arrivals {
    background: var(--cream-light);
}

.arrivals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.arrival-card {
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-smooth);
    position: relative;
}

.arrival-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-strong);
}

.arrival-card-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 5;
    background: linear-gradient(135deg, var(--pink-dark), var(--pink-medium));
    color: white;
    padding: 5px 15px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.arrival-card-badge.new {
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
}

.arrival-card-badge.hot {
    background: linear-gradient(135deg, #e74c3c, #e67e22);
}

.arrival-card-wishlist {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 5;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: var(--medium-gray);
    transition: var(--transition-smooth);
    backdrop-filter: blur(10px);
}

.arrival-card-wishlist:hover,
.arrival-card-wishlist.active {
    background: var(--pink-dark);
    color: white;
}

.arrival-card-image {
    position: relative;
    height: 350px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f4f0 0%, #f0e8e0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.arrival-card-image img {
    height: 80%;
    object-fit: contain;
    transition: transform 0.6s ease;
}

.arrival-card:hover .arrival-card-image img {
    transform: scale(1.08) rotate(-2deg);
}

.arrival-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    display: flex;
    gap: 10px;
    justify-content: center;
    transform: translateY(100%);
    transition: transform 0.4s ease;
}

.arrival-card:hover .arrival-card-overlay {
    transform: translateY(0);
}

.overlay-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--white);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: var(--dark-gray);
    transition: var(--transition-smooth);
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
}

.overlay-btn:hover {
    background: var(--gold-primary);
    color: white;
}

.arrival-card-body {
    padding: 25px;
}

.arrival-card-category {
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--gold-primary);
    font-weight: 600;
    margin-bottom: 8px;
}

.arrival-card-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    color: var(--dark-gray);
    margin-bottom: 8px;
}

.arrival-card-notes {
    font-size: 0.8rem;
    color: var(--medium-gray);
    margin-bottom: 12px;
    font-style: italic;
}

.arrival-card-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 15px;
}

.arrival-card-rating .stars {
    color: var(--star-gold);
    font-size: 0.85rem;
}

.arrival-card-rating .count {
    font-size: 0.75rem;
    color: var(--medium-gray);
}

.arrival-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.arrival-card-price {
    display: flex;
    flex-direction: column;
}

.arrival-card-price .current {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    color: var(--gold-dark);
    font-weight: 600;
}

.arrival-card-price .original {
    font-size: 0.8rem;
    color: var(--medium-gray);
    text-decoration: line-through;
}

.btn-add-cart {
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-smooth);
    letter-spacing: 1px;
}

.btn-add-cart:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(201, 169, 110, 0.4);
}

/* ============================================
   FEATURED BANNER
   ============================================ */
.featured-banner {
    position: relative;
    margin: 0 30px;
    max-width: 1340px;
    margin-left: auto;
    margin-right: auto;
    border-radius: 30px;
    overflow: hidden;
    min-height: 500px;
    display: flex;
    align-items: center;
}

.featured-banner-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.featured-banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(26,26,26,0.7) 0%, rgba(45,45,45,0.3) 100%);
}

.featured-banner-content {
    position: relative;
    z-index: 5;
    padding: 60px 80px;
    max-width: 600px;
}

.featured-banner-tag {
    display: inline-block;
    background: var(--gold-primary);
    color: white;
    padding: 6px 20px;
    border-radius: 50px;
    font-size: 0.7rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 20px;
}

.featured-banner-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: white;
    margin-bottom: 15px;
    line-height: 1.2;
}

.featured-banner-desc {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
    margin-bottom: 30px;
    line-height: 1.7;
}

.featured-banner-price {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: var(--gold-shimmer);
    margin-bottom: 25px;
}

.btn-banner {
    background: var(--white);
    color: var(--dark-gray);
    border: none;
    padding: 14px 40px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition-smooth);
}

.btn-banner:hover {
    background: var(--gold-primary);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* ============================================
   CATEGORIES SECTION
   ============================================ */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
}

.category-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    height: 280px;
    cursor: pointer;
    transition: var(--transition-smooth);
}

.category-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-strong);
}

.category-card-bg {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.category-card:hover .category-card-bg {
    transform: scale(1.1);
}

.category-card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 60%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 25px;
}

.category-card-title {
    font-family: 'Playfair Display', serif;
    color: white;
    font-size: 1.3rem;
    margin-bottom: 5px;
}

.category-card-count {
    color: var(--gold-light);
    font-size: 0.8rem;
    letter-spacing: 1px;
}

/* ============================================
   REVIEWS / AVIS CLIENTS
   ============================================ */
.reviews-section {
    background: linear-gradient(135deg, var(--cream) 0%, var(--pink-soft) 50%, var(--cream) 100%);
    padding: 100px 0;
}

.reviews-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 30px;
}

.reviews-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 60px;
    margin-bottom: 60px;
    flex-wrap: wrap;
}

.reviews-overall {
    text-align: center;
}

.reviews-overall-score {
    font-family: 'Playfair Display', serif;
    font-size: 4rem;
    color: var(--gold-dark);
    line-height: 1;
}

.reviews-overall-stars {
    color: var(--star-gold);
    font-size: 1.2rem;
    margin: 10px 0;
}

.reviews-overall-count {
    font-size: 0.85rem;
    color: var(--medium-gray);
}

.reviews-bars {
    flex: 1;
    max-width: 400px;
}

.review-bar-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.review-bar-label {
    font-size: 0.85rem;
    color: var(--dark-gray);
    min-width: 20px;
    font-weight: 600;
}

.review-bar-track {
    flex: 1;
    height: 8px;
    background: rgba(0,0,0,0.08);
    border-radius: 10px;
    overflow: hidden;
}

.review-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--gold-primary), var(--gold-shimmer));
    border-radius: 10px;
    transition: width 1s ease;
}

.review-bar-percent {
    font-size: 0.8rem;
    color: var(--medium-gray);
    min-width: 30px;
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.review-card {
    background: var(--white);
    border-radius: 20px;
    padding: 30px;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-smooth);
    position: relative;
}

.review-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.review-card-quote {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 3rem;
    color: var(--gold-light);
    opacity: 0.3;
    font-family: 'Playfair Display', serif;
    line-height: 1;
}

.review-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.review-avatar {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 600;
    flex-shrink: 0;
}

.review-info h4 {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem;
    color: var(--dark-gray);
    margin-bottom: 3px;
}

.review-info .verified {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    color: #27ae60;
    font-weight: 500;
}

.review-info .verified i {
    font-size: 0.7rem;
}

.review-stars {
    color: var(--star-gold);
    font-size: 0.9rem;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 3px;
}

.review-text {
    font-size: 0.9rem;
    color: var(--medium-gray);
    line-height: 1.7;
    font-style: italic;
}

.review-product {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 15px;
    padding: 6px 15px;
    background: var(--pink-soft);
    border-radius: 50px;
    font-size: 0.75rem;
    color: var(--gold-dark);
    font-weight: 500;
}

.review-date {
    font-size: 0.75rem;
    color: var(--light-gray);
    margin-top: 10px;
}

/* ============================================
   BESTSELLERS
   ============================================ */
.bestsellers-scroll {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    padding-bottom: 20px;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.bestsellers-scroll::-webkit-scrollbar {
    display: none;
}

.bestseller-card {
    min-width: 280px;
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-smooth);
    scroll-snap-align: start;
    position: relative;
}

.bestseller-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-strong);
}

.bestseller-rank {
    position: absolute;
    top: 15px;
    left: 15px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
    z-index: 5;
}

.bestseller-image {
    height: 300px;
    background: linear-gradient(135deg, #f8f4f0 0%, #f0e8e0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.bestseller-image img {
    height: 75%;
    object-fit: contain;
    transition: transform 0.5s ease;
}

.bestseller-card:hover .bestseller-image img {
    transform: scale(1.08);
}

.bestseller-body {
    padding: 20px;
    text-align: center;
}

.bestseller-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    color: var(--dark-gray);
    margin-bottom: 5px;
}

.bestseller-price {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    color: var(--gold-dark);
    font-weight: 600;
    margin-bottom: 10px;
}

.bestseller-stars {
    color: var(--star-gold);
    font-size: 0.8rem;
}

/* ============================================
   NEWSLETTER
   ============================================ */
.newsletter {
    background: linear-gradient(135deg, var(--dark-gray) 0%, var(--black) 100%);
    padding: 80px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.newsletter-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.05;
    background-image: radial-gradient(circle at 30% 40%, var(--gold-primary) 1px, transparent 1px),
                      radial-gradient(circle at 70% 60%, var(--gold-primary) 1px, transparent 1px);
    background-size: 40px 40px, 50px 50px;
}

.newsletter-content {
    position: relative;
    z-index: 5;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-icon {
    font-size: 2.5rem;
    color: var(--gold-primary);
    margin-bottom: 20px;
}

.newsletter-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: white;
    margin-bottom: 10px;
}

.newsletter-desc {
    color: rgba(255,255,255,0.6);
    margin-bottom: 30px;
    font-size: 0.95rem;
}

.newsletter-form {
    display: flex;
    gap: 12px;
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-input {
    flex: 1;
    padding: 15px 25px;
    border: 1px solid rgba(201, 169, 110, 0.3);
    border-radius: 50px;
    background: rgba(255,255,255,0.05);
    color: white;
    font-size: 0.9rem;
    outline: none;
    transition: var(--transition-smooth);
}

.newsletter-input::placeholder {
    color: rgba(255,255,255,0.4);
}

.newsletter-input:focus {
    border-color: var(--gold-primary);
    background: rgba(255,255,255,0.1);
}

.newsletter-btn {
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
    color: white;
    border: none;
    padding: 15px 35px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-smooth);
    white-space: nowrap;
}

.newsletter-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(201, 169, 110, 0.4);
}

/* ============================================
   SOCIAL MEDIA BAR
   ============================================ */
.social-bar {
    position: fixed;
    right: 25px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.social-link {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    text-decoration: none;
    transition: var(--transition-smooth);
    box-shadow: 0 3px 15px rgba(0,0,0,0.2);
    position: relative;
}

.social-link:hover {
    transform: scale(1.15) translateX(-5px);
}

.social-link .tooltip {
    position: absolute;
    right: 60px;
    background: var(--dark-gray);
    color: white;
    padding: 6px 15px;
    border-radius: 5px;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition-smooth);
}

.social-link:hover .tooltip {
    opacity: 1;
    visibility: visible;
}

.social-link.whatsapp { background: linear-gradient(135deg, #25D366, #128C7E); }
.social-link.facebook { background: linear-gradient(135deg, #1877F2, #0C5DC7); }
.social-link.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }

/* ============================================
   FOOTER
   ============================================ */
.footer {
    background: linear-gradient(180deg, var(--cream) 0%, var(--white) 100%);
    padding: 80px 30px 30px;
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 50px;
    margin-bottom: 60px;
}

.footer-brand-logo {
    width: 150px;
    margin-bottom: 20px;
}

.footer-brand-desc {
    color: var(--medium-gray);
    font-size: 0.9rem;
    line-height: 1.7;
    margin-bottom: 25px;
}

.footer-social-row {
    display: flex;
    gap: 12px;
}

.footer-social-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
    text-decoration: none;
    transition: var(--transition-smooth);
}

.footer-social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.footer-social-btn.whatsapp { background: #25D366; }
.footer-social-btn.facebook { background: #1877F2; }
.footer-social-btn.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }

.footer-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    color: var(--dark-gray);
    margin-bottom: 20px;
}

.footer-links {
    list-style: none;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: var(--medium-gray);
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition-smooth);
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.footer-links a:hover {
    color: var(--gold-primary);
    padding-left: 5px;
}

.footer-bottom {
    border-top: 1px solid var(--light-gray);
    padding-top: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-copyright {
    font-size: 0.8rem;
    color: var(--medium-gray);
}

.footer-payments {
    display: flex;
    gap: 10px;
    align-items: center;
}

.footer-payments i {
    font-size: 1.8rem;
    color: var(--medium-gray);
}

/* ============================================
   SCROLL TO TOP
   ============================================ */
.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 998;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: var(--transition-smooth);
    box-shadow: 0 5px 20px rgba(201, 169, 110, 0.4);
}

.scroll-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.scroll-top:hover {
    transform: translateY(-5px);
}

/* ============================================
   ANIMATIONS
   ============================================ */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.animate-on-scroll.animated {
    opacity: 1;
    transform: translateY(0);
}

/* ============================================
   TOAST NOTIFICATION
   ============================================ */
.toast-container {
    position: fixed;
    top: 100px;
    right: 30px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toast {
    background: var(--white);
    border-radius: 15px;
    padding: 15px 25px;
    box-shadow: var(--shadow-strong);
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 300px;
    animation: toastSlideIn 0.5s ease forwards;
    border-left: 4px solid var(--gold-primary);
}

.toast.removing {
    animation: toastSlideOut 0.4s ease forwards;
}

.toast-icon {
    font-size: 1.3rem;
    color: var(--gold-primary);
}

.toast-message {
    font-size: 0.9rem;
    color: var(--dark-gray);
}

@keyframes toastSlideIn {
    from { opacity: 0; transform: translateX(100%); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes toastSlideOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(100%); }
}

/* ============================================
   PRODUCT MODAL
   ============================================ */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
    z-index: 10001;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: var(--white);
    border-radius: 25px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.9) translateY(20px);
    transition: var(--transition-smooth);
    box-shadow: var(--shadow-strong);
}

.modal-overlay.active .modal {
    transform: scale(1) translateY(0);
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--white);
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--dark-gray);
    z-index: 10;
    transition: var(--transition-smooth);
}

.modal-close:hover {
    background: var(--pink-soft);
    transform: rotate(90deg);
}

.modal-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.modal-image {
    background: linear-gradient(135deg, #f8f4f0 0%, #f0e8e0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    border-radius: 25px 0 0 25px;
    min-height: 400px;
}

.modal-image img {
    max-height: 350px;
    object-fit: contain;
}

.modal-details {
    padding: 40px;
}

.modal-category {
    font-size: 0.7rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--gold-primary);
    font-weight: 600;
    margin-bottom: 10px;
}

.modal-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--dark-gray);
    margin-bottom: 10px;
}

.modal-stars {
    color: var(--star-gold);
    font-size: 0.9rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-stars span {
    color: var(--medium-gray);
    font-size: 0.8rem;
}

.modal-price {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: var(--gold-dark);
    margin-bottom: 20px;
}

.modal-desc {
    font-size: 0.9rem;
    color: var(--medium-gray);
    line-height: 1.7;
    margin-bottom: 25px;
}

.modal-notes {
    margin-bottom: 25px;
}

.modal-notes-title {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--dark-gray);
    margin-bottom: 10px;
}

.modal-notes-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.modal-note-tag {
    padding: 5px 15px;
    background: var(--pink-soft);
    border-radius: 50px;
    font-size: 0.75rem;
    color: var(--gold-dark);
}

.modal-size-options {
    margin-bottom: 25px;
}

.modal-size-label {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--dark-gray);
    margin-bottom: 10px;
}

.modal-sizes {
    display: flex;
    gap: 10px;
}

.modal-size-btn {
    padding: 8px 20px;
    border: 2px solid var(--light-gray);
    border-radius: 50px;
    background: transparent;
    cursor: pointer;
    font-size: 0.85rem;
    transition: var(--transition-smooth);
}

.modal-size-btn:hover,
.modal-size-btn.active {
    border-color: var(--gold-primary);
    background: var(--gold-primary);
    color: white;
}

.modal-actions {
    display: flex;
    gap: 12px;
}

.modal-add-cart {
    flex: 1;
    background: linear-gradient(135deg, var(--gold-primary), var(--gold-shimmer));
    color: white;
    border: none;
    padding: 14px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-smooth);
    letter-spacing: 1px;
}

.modal-add-cart:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 20px rgba(201, 169, 110, 0.4);
}

.modal-wishlist-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid var(--light-gray);
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: var(--medium-gray);
    transition: var(--transition-smooth);
}

.modal-wishlist-btn:hover {
    border-color: var(--pink-dark);
    color: var(--pink-dark);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1024px) {
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .featured-banner-content {
        padding: 40px;
    }

    .nav-center {
        display: none;
    }

    .nav-left {
        margin-left: 70px;
    }
}

@media (max-width: 768px) {
    .section {
        padding: 60px 20px;
    }

    .categories-grid {
        grid-template-columns: 1fr;
    }

    .reviews-grid {
        grid-template-columns: 1fr;
    }

    .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .social-bar {
        right: 15px;
        gap: 8px;
    }

    .social-link {
        width: 42px;
        height: 42px;
        font-size: 1rem;
    }

    .social-link .tooltip {
        display: none;
    }

    .featured-banner {
        margin: 0 15px;
        min-height: 400px;
    }

    .featured-banner-content {
        padding: 30px;
    }

    .featured-banner-title {
        font-size: 1.8rem;
    }

    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }

    .nav-left {
        margin-left: 60px;
    }

    .hero-watermark {
        width: 300px;
    }

    .arrivals-grid {
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }
}

@media (max-width: 480px) {
    .hamburger-btn {
        top: 15px;
        left: 15px;
        width: 44px;
        height: 44px;
    }

    .nav-container {
        height: 60px;
        padding: 0 15px;
    }

    .nav-logo {
        height: 35px;
    }

    .nav-icon {
        width: 36px;
        height: 36px;
        font-size: 0.95rem;
    }

    .arrivals-grid {
        grid-template-columns: 1fr;
    }

    .bestseller-card {
        min-width: 250px;
    }

    .modal-body {
        grid-template-columns: 1fr;
    }

    .modal-image {
        border-radius: 25px 25px 0 0;
        min-height: 250px;
        padding: 30px;
    }

    .modal-image img {
        max-height: 200px;
    }

    .modal-details {
        padding: 25px;
    }
}


              </style>
                        </head>
                        <body>
                            <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SheyLuna Parfums - Boutique Officielle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<div id="cartDrawer" class="cart-drawer">
  <div class="cart-header">
    <h2>🛍️ Mon Panier</h2>
    <button onclick="closeCart()">×</button>
  </div>

  <div id="cartItems"></div>

  <button class="checkout-btn" onclick="goToCommande()">Commander</button>
  <button class="clear-cart-btn" onclick="clearCart()">Vider le panier</button>
</div>
<body>

    <!-- PRELOADER -->
    <div class="preloader" id="preloader">
        <img src="images/site/WhatsApp Image 2026-05-03 at 15.42.18.jpeg" alt="SheyLuna" class="preloader-logo">
        <div class="preloader-text">SHEY LUNA</div>
        <div class="preloader-bar">
            <div class="preloader-bar-inner"></div>
        </div>
    </div>

    <!-- SIDEBAR OVERLAY -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR NAVIGATION -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="images/site/WhatsApp Image 2026-05-03 at 15.42.18.jpeg" alt="SheyLuna" class="sidebar-logo">
            <div class="sidebar-title">SheyLuna</div>
            <div class="sidebar-subtitle">Parfums</div>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
        </div>

        <div class="sidebar-nav">
            <div class="sidebar-section-title">Navigation</div>
            <a href="#accueil" class="sidebar-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="#nouveautes-femme" class="sidebar-link"><i class="fas fa-spa"></i> Parfums Femme <span class="badge">NEW</span></a>
            <a href="#nouveautes-homme" class="sidebar-link"><i class="fas fa-male"></i> Parfums Homme <span class="badge">NEW</span></a>
            <a href="#collections" class="sidebar-link"><i class="fas fa-gem"></i> Collections</a>
            <a href="#bestsellers" class="sidebar-link"><i class="fas fa-crown"></i> Best-sellers</a>
            <a href="#avis" class="sidebar-link"><i class="fas fa-star"></i> Avis Clients</a>
            <a href="#contact" class="sidebar-link"><i class="fas fa-envelope"></i> Contact</a>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section-title">Parfums Femme</div>
<?php foreach ($produits as $idx => $prod): if ($prod['genre'] === 'Femme') echo sheyluna_sidebar_item($prod, $idx); endforeach; ?>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section-title">Parfums Homme</div>
<?php foreach ($produits as $idx => $prod): if ($prod['genre'] === 'Homme') echo sheyluna_sidebar_item($prod, $idx); endforeach; ?>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section-title">Informations</div>
            <a href="#" class="sidebar-link"><i class="fas fa-shipping-fast"></i> Livraison</a>
            <a href="#" class="sidebar-link"><i class="fas fa-undo"></i> Retours</a>
            <a href="#" class="sidebar-link"><i class="fas fa-question-circle"></i> FAQ</a>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-social">
                <a href="#" class="whatsapp"><i class="fab fa-whatsapp"></i></a>
                <a href="#" class="facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="instagram"><i class="fab fa-instagram"></i></a>
            </div>
            <div class="sidebar-footer-text">© 2026 SheyLuna Parfums - Maroc</div>
        </div>
    </nav>

    <!-- HAMBURGER BUTTON -->
    <button class="hamburger-btn" id="hamburgerBtn">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- SOCIAL MEDIA BAR -->
    <div class="social-bar" id="socialBar">
        <a href="<?php echo htmlspecialchars(param('social_whatsapp', '#')); ?>" target="_blank" class="social-link whatsapp">
            <i class="fab fa-whatsapp"></i>
            <span class="tooltip">WhatsApp</span>
        </a>
        <a href="<?php echo htmlspecialchars(param('social_facebook', '#')); ?>" target="_blank" class="social-link facebook">
            <i class="fab fa-facebook-f"></i>
            <span class="tooltip">Facebook</span>
        </a>
        <a href="<?php echo htmlspecialchars(param('social_instagram', '#')); ?>" target="_blank" class="social-link instagram">
            <i class="fab fa-instagram"></i>
            <span class="tooltip">Instagram</span>
        </a>
    </div>

    <!-- TOP BAR -->
    <div class="top-bar">
        <i class="fas fa-gift"></i>
        <?php echo htmlspecialchars(param('promo_texte')); ?>
    </div>

    <!-- MAIN NAVIGATION -->
    <header class="main-nav" id="mainNav">
        <div class="nav-container">
            <div class="nav-left">
                <img src="images/site/WhatsApp Image 2026-06-09 at 14.42.27.jpeg" alt="SheyLuna" class="nav-logo">
            </div>
            <div class="nav-center">
                <a href="#accueil" class="nav-link">Accueil</a>
                <a href="#nouveautes-femme" class="nav-link">Femme</a>
                <a href="#nouveautes-homme" class="nav-link">Homme</a>
                <a href="#collections" class="nav-link">Collections</a>
                <a href="#bestsellers" class="nav-link">Best-sellers</a>
                <a href="#avis" class="nav-link">Avis</a>
            </div>
            <div class="nav-right">
                <button class="nav-icon" title="Rechercher"><i class="fas fa-search"></i></button>
                <button class="nav-icon" title="Favoris"><i class="fas fa-heart"></i></button>
                <button class="nav-icon" title="Panier" onclick="showCart()">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </button>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="accueil">
        <div class="hero-bg-pattern"></div>
        <img src="images/site/WhatsApp Image 2026-06-09 at 14.42.27.jpeg" alt="SheyLuna" class="hero-watermark">

        <div class="hero-floating-elements">
            <div class="floating-element">✨</div>
            <div class="floating-element">✨</div>
            <div class="floating-element">🌸</div>
            <div class="floating-element"></div>
            <div class="floating-element">🌸</div>
        </div>

        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-sparkles"></i>
                Collection 2026 — Disponible maintenant
            </div>
            <h1 class="hero-title">
                Découvrez l'Essence<br>
                <span class="highlight">SheyLuna Parfums</span>
            </h1>
            <p class="hero-subtitle">
                « Chaque parfum raconte une histoire unique, composée avec passion et élégance »
            </p>
            <div class="hero-buttons">
                <a href="#nouveautes-femme" class="btn-primary">
                    <i class="fas fa-gem"></i> Collection Femme
                </a>
                <a href="#nouveautes-homme" class="btn-secondary">
                    <i class="fas fa-male"></i> Collection Homme
                </a>
            </div>
        </div>

        <div class="hero-scroll">
            <span>Défiler</span>
            <div class="hero-scroll-line"></div>
        </div>
    </section>

    <!-- NOUVEAUTÉS FEMME -->
    <section class="new-arrivals section" id="nouveautes-femme">
        <div class="section-header animate-on-scroll">
            <div class="section-tag"><span>Nouveautés Femme 2026</span></div>
            <h2 class="section-title">Parfums pour Elle</h2>
            <p class="section-desc">Des fragrances féminines élégantes, créées avec les matières premières les plus nobles</p>
        </div>

        <div class="arrivals-grid">
<?php foreach ($produits as $idx => $prod): if ($prod['genre'] === 'Femme') echo sheyluna_card($prod, $idx); endforeach; ?>
        </div>
    </section>

    <!-- NOUVEAUTÉS HOMME -->
    <section class="new-arrivals section" id="nouveautes-homme" style="background: linear-gradient(180deg, var(--cream) 0%, var(--cream-light) 100%); max-width: 100%; padding-left: 0; padding-right: 0;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 30px;">
            <div class="section-header animate-on-scroll">
                <div class="section-tag"><span>Nouveautés Homme 2026</span></div>
                <h2 class="section-title">Parfums pour Lui</h2>
                <p class="section-desc">Des fragrances masculines puissantes et raffinées pour l'homme moderne</p>
            </div>

            <div class="arrivals-grid">
<?php foreach ($produits as $idx => $prod): if ($prod['genre'] === 'Homme') echo sheyluna_card($prod, $idx); endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FEATURED BANNER -->
    <div class="featured-banner animate-on-scroll">
        <img src="<?php echo htmlspecialchars(param('banner_image')); ?>" alt="Collection Signature" class="featured-banner-bg">
        <div class="featured-banner-overlay"></div>
        <div class="featured-banner-content">
            <div class="featured-banner-tag"><?php echo htmlspecialchars(param('banner_tag')); ?></div>
            <h2 class="featured-banner-title"><?php echo nl2br(htmlspecialchars(param('banner_titre'))); ?></h2>
            <p class="featured-banner-desc"><?php echo htmlspecialchars(param('banner_desc')); ?></p>
            <div class="featured-banner-price"><?php echo htmlspecialchars(param('banner_prix')); ?> <?php if (param('banner_prix_original')): ?><span style="font-size:1rem;color:rgba(255,255,255,0.5);text-decoration:line-through;"><?php echo htmlspecialchars(param('banner_prix_original')); ?></span><?php endif; ?></div>
            <button class="btn-banner" onclick="addToCart('Coffret Signature')"><?php echo htmlspecialchars(param('banner_bouton')); ?></button>
        </div>
    </div>

    <!-- CATEGORIES -->
    <section class="section" id="collections">
        <div class="section-header animate-on-scroll">
            <div class="section-tag"><span>Nos Collections</span></div>
            <h2 class="section-title">Explorez par Univers</h2>
            <p class="section-desc">Chaque collection est une invitation à voyager à travers les senteurs du monde</p>
        </div>

        <div class="categories-grid">
<?php foreach ($categories as $cat) echo sheyluna_category_card($cat); ?>
        </div>
    </section>

    <!-- BESTSELLERS -->
    <section class="section" id="bestsellers" style="background: var(--cream); max-width: 100%; padding-left: 0; padding-right: 0;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 30px;">
            <div class="section-header animate-on-scroll">
                <div class="section-tag"><span>Les Plus Aimés</span></div>
                <h2 class="section-title">Best-sellers</h2>
                <p class="section-desc">Les parfums préférés de nos clients, plébiscités pour leur qualité exceptionnelle</p>
            </div>
        </div>

        <div class="bestsellers-scroll" style="padding-left: 30px; padding-right: 30px; max-width: 1400px; margin: 0 auto;">
<?php $r = 1; foreach ($bestsellers as $bp) echo sheyluna_bestseller_card($bp, $r++); ?>
        </div>
    </section>

    <!-- REVIEWS / AVIS CLIENTS -->
    <section class="reviews-section" id="avis">

        <div class="reviews-container">
            <div class="section-header animate-on-scroll">
                <div class="section-tag"><span>Avis Vérifiés</span></div>
                <h2 class="section-title">Ce Que Disent Nos Clients</h2>
                <p class="section-desc">Des avis authentiques de clients satisfaits qui partagent leur expérience SheyLuna</p>
            </div>

            <div class="reviews-stats animate-on-scroll">
                <div class="reviews-overall">
                    <div class="reviews-overall-score">4.8</div>
                    <div class="reviews-overall-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="reviews-overall-count">Basé sur 847 avis vérifiés</div>
                </div>
                <div class="reviews-bars">
                    <div class="review-bar-item">
                        <span class="review-bar-label">5</span>
                        <div class="review-bar-track"><div class="review-bar-fill" style="width: 78%"></div></div>
                        <span class="review-bar-percent">78%</span>
                    </div>
                    <div class="review-bar-item">
                        <span class="review-bar-label">4</span>
                        <div class="review-bar-track"><div class="review-bar-fill" style="width: 15%"></div></div>
                        <span class="review-bar-percent">15%</span>
                    </div>
                    <div class="review-bar-item">
                        <span class="review-bar-label">3</span>
                        <div class="review-bar-track"><div class="review-bar-fill" style="width: 4%"></div></div>
                        <span class="review-bar-percent">4%</span>
                    </div>
                    <div class="review-bar-item">
                        <span class="review-bar-label">2</span>
                        <div class="review-bar-track"><div class="review-bar-fill" style="width: 2%"></div></div>
                        <span class="review-bar-percent">2%</span>
                    </div>
                    <div class="review-bar-item">
                        <span class="review-bar-label">1</span>
                        <div class="review-bar-track"><div class="review-bar-fill" style="width: 1%"></div></div>
                        <span class="review-bar-percent">1%</span>
                    </div>
                </div>
            </div>

            <div class="reviews-grid">
                <div class="review-card animate-on-scroll">
                    <div class="review-card-quote">"</div>
                    <div class="review-card-header">
                        <div class="review-avatar">S</div>
                        <div class="review-info">
                            <h4>Sophie M.</h4>
                            <div class="verified"><i class="fas fa-check-circle"></i> Achat vérifié</div>
                        </div>
                    </div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">« Faraway Avon est devenu mon parfum signature ! Une odeur de rose délicate et envoûtante. La tenue est incroyable, je reçois des compliments partout. Livraison rapide à Casablanca. Merci SheyLuna ! »</p>
                    <div class="review-product"><i class="fas fa-spray-can"></i> Faraway Avon — 50ml</div>
                    <div class="review-date">Il y a 3 jours</div>
                </div>

                <div class="review-card animate-on-scroll">
                    <div class="review-card-quote">"</div>
                    <div class="review-card-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, var(--pink-dark), var(--pink-medium));">A</div>
                        <div class="review-info">
                            <h4>Amina K.</h4>
                            <div class="verified"><i class="fas fa-check-circle"></i> Achat vérifié</div>
                        </div>
                    </div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">« Baccarat Rouge 540 est tout simplement magnifique ! Un parfum riche, complexe et d'une tenue exceptionnelle. Mon mari l'adore aussi. Le packaging est très luxueux. Je recommande vivement ! »</p>
                    <div class="review-product"><i class="fas fa-spray-can"></i> Baccarat Rouge 540 — 50ml</div>
                    <div class="review-date">Il y a 1 semaine</div>
                </div>

                <div class="review-card animate-on-scroll">
                    <div class="review-card-quote">"</div>
                    <div class="review-card-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #3498db, #2980b9);">Y</div>
                        <div class="review-info">
                            <h4>Youssef B.</h4>
                            <div class="verified"><i class="fas fa-check-circle"></i> Achat vérifié</div>
                        </div>
                    </div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="review-text">« Versace Eros est le meilleur parfum que j'ai porté ! Les notes boisées et l'encens créent une composition masculine très sophistiquée. La tenue dure toute la journée. Parfait pour le travail et les soirées. »</p>
                    <div class="review-product"><i class="fas fa-spray-can"></i> Versace Eros — 50ml</div>
                    <div class="review-date">Il y a 2 semaines</div>
                </div>

                <div class="review-card animate-on-scroll">
                    <div class="review-card-quote">"</div>
                    <div class="review-card-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">F</div>
                        <div class="review-info">
                            <h4>Fatima Z.</h4>
                            <div class="verified"><i class="fas fa-check-circle"></i> Achat vérifié</div>
                        </div>
                    </div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">« J'ai offert le coffret Signature à mon mari et il est ravi ! Chaque parfum est de très haute qualité. Le service client est également irréprochable. Livraison à Marrakech meme pas 24h. 5 étoiles méritées ! »</p>
                    <div class="review-product"><i class="fas fa-spray-can"></i> Coffret Signature</div>
                    <div class="review-date">Il y a 3 semaines</div>
                </div>

                <div class="review-card animate-on-scroll">
                    <div class="review-card-quote">"</div>
                    <div class="review-card-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">K</div>
                        <div class="review-info">
                            <h4>Karim L.</h4>
                            <div class="verified"><i class="fas fa-check-circle"></i> Achat vérifié</div>
                        </div>
                    </div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">« Valentino Umo est un parfum puissant et élégant. Les notes de poivre noir et cuir sont parfaitement dosées. C'est mon troisième achat chez SheyLuna et je suis toujours satisfait. Qualité au rendez-vous ! »</p>
                    <div class="review-product"><i class="fas fa-spray-can"></i> Valentino Umo — 50ml</div>
                    <div class="review-date">Il y a 1 mois</div>
                </div>

                <div class="review-card animate-on-scroll">
                    <div class="review-card-quote">"</div>
                    <div class="review-card-header">
                        <div class="review-avatar" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">N</div>
                        <div class="review-info">
                            <h4>Nadia R.</h4>
                            <div class="verified"><i class="fas fa-check-circle"></i> Achat vérifié</div>
                        </div>
                    </div>
                    <div class="review-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                    </div>
                    <p class="review-text">« Kayali Vanille 28 est frais et féminin, parfait pour le printemps et l'été. Les notes florales sont délicates sans être envahissantes. Excellent rapport qualité-prix. Je recommande ! »</p>
                    <div class="review-product"><i class="fas fa-spray-can"></i> Kayali Vanille 28 — 30ml</div>
                    <div class="review-date">Il y a 1 mois</div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEWSLETTER -->
    <section class="newsletter" id="contact">
        <div class="newsletter-pattern"></div>
        <div class="newsletter-content animate-on-scroll">
            <div class="newsletter-icon"><i class="fas fa-envelope-open-text"></i></div>
            <h2 class="newsletter-title">Rejoignez l'Univers SheyLuna</h2>
            <p class="newsletter-desc">Inscrivez-vous pour recevoir nos nouveautés, offres exclusives et conseils parfum en avant-première</p>
            <form class="newsletter-form" onsubmit="handleNewsletter(event)">
                <input type="email" class="newsletter-input" placeholder="Votre adresse email..." required>
                <button type="submit" class="newsletter-btn">S'inscrire</button>
            </form>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div>
                    <img src="images/site/WhatsApp Image 2026-05-03 at 15.42.18.jpeg" alt="SheyLuna" class="footer-brand-logo">
                    <p class="footer-brand-desc"><?php echo htmlspecialchars(param('footer_description')); ?></p>
                    <div class="footer-social-row">
                        <a href="<?php echo htmlspecialchars(param('social_whatsapp', '#')); ?>" target="_blank" class="footer-social-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="<?php echo htmlspecialchars(param('social_facebook', '#')); ?>" target="_blank" class="footer-social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo htmlspecialchars(param('social_instagram', '#')); ?>" target="_blank" class="footer-social-btn instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="footer-title">Boutique</h3>
                    <ul class="footer-links">
                        <li><a href="#">Parfums Femme</a></li>
                        <li><a href="#">Parfums Homme</a></li>
                        <li><a href="#">Coffrets</a></li>
                        <li><a href="#">Éditions Limitées</a></li>
                        <li><a href="#">Nouveautés</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Informations</h3>
                    <ul class="footer-links">
                        <li><a href="#">Notre Histoire</a></li>
                        <li><a href="#">Livraison & Retours</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Programme Fidélité</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Contact</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(param('contact_adresse')); ?></a></li>
                        <li><a href="tel:<?php echo htmlspecialchars(param('contact_tel_lien')); ?>"><i class="fas fa-phone"></i> <?php echo htmlspecialchars(param('contact_tel')); ?></a></li>
                        <li><a href="mailto:<?php echo htmlspecialchars(param('contact_email')); ?>"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars(param('contact_email')); ?></a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> <?php echo htmlspecialchars(param('contact_horaires')); ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-copyright"><?php echo htmlspecialchars(param('copyright')); ?></div>
                <div class="footer-payments">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fas fa-money-bill-wave" title="Paiement à la livraison"></i>
                    <i class="fas fa-mobile-alt" title="Paiement mobile"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- SCROLL TO TOP -->
    <button class="scroll-top" id="scrollTop"><i class="fas fa-chevron-up"></i></button>

    <!-- TOAST CONTAINER -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- PRODUCT MODAL -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            <div class="modal-body">
                <div class="modal-image">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="modal-details">
                    <div class="modal-category" id="modalCategory"></div>
                    <h2 class="modal-name" id="modalName"></h2>
                    <div class="modal-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        <span id="modalReviewCount"></span>
                    </div>
                    <div class="modal-price" id="modalPrice"></div>
                    <p class="modal-desc" id="modalDesc"></p>
                    <div class="modal-notes">
                        <div class="modal-notes-title">Notes olfactives</div>
                        <div class="modal-notes-tags" id="modalNotes"></div>
                    </div>
                    <div class="modal-size-options">
                        <div class="modal-size-label">Format</div>
                        <div class="modal-sizes">
                            <button class="modal-size-btn active" onclick="selectSize(this)">30ml</button>
                            <button class="modal-size-btn" onclick="selectSize(this)">50ml</button>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="modal-add-cart" id="modalAddCart" onclick="addToCartFromModal()">
                            <i class="fas fa-shopping-bag"></i> Ajouter au Panier
                        </button>
                        <button class="modal-wishlist-btn"><i class="far fa-heart"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>



              <script>
                              /* ============================================
   SHEYLUNA PARFUMS - JAVASCRIPT PRINCIPAL
   ============================================ */

// Product Data - 8 parfums (4 Femme + 4 Homme)
const productsData = <?php echo json_encode($pdata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

// Cart count
var cartItemCount = 0;

// ============================================
// PRELOADER
// ============================================
window.addEventListener('load', function() {
    setTimeout(function() {
        var preloaderEl = document.getElementById('preloader');
        if (preloaderEl) {
            preloaderEl.classList.add('hidden');
        }
    }, 2200);
});

// ============================================
// HAMBURGER / SIDEBAR
// ============================================
var hamburgerBtn = document.getElementById('hamburgerBtn');
var sidebar = document.getElementById('sidebar');
var sidebarOverlay = document.getElementById('sidebarOverlay');
var sidebarClose = document.getElementById('sidebarClose');

if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        hamburgerBtn.classList.toggle('active');
    });
}

if (sidebarClose) {
    sidebarClose.addEventListener('click', closeSidebar);
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeSidebar);
}

function closeSidebar() {
    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
    hamburgerBtn.classList.remove('active');
}

// Close sidebar on link click
var sidebarLinks = document.querySelectorAll('.sidebar-link');
sidebarLinks.forEach(function(link) {
    link.addEventListener('click', function() {
        closeSidebar();
    });
});

// ============================================
// STICKY NAV
// ============================================
var mainNav = document.getElementById('mainNav');
window.addEventListener('scroll', function() {
    if (window.scrollY > 100) {
        mainNav.classList.add('scrolled');
    } else {
        mainNav.classList.remove('scrolled');
    }
});

// ============================================
// SCROLL TO TOP
// ============================================
var scrollTopBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', function() {
    if (window.scrollY > 500) {
        scrollTopBtn.classList.add('visible');
    } else {
        scrollTopBtn.classList.remove('visible');
    }
});

scrollTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ============================================
// SCROLL ANIMATIONS
// ============================================
function handleScrollAnimations() {
    var elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach(function(el) {
        var rect = el.getBoundingClientRect();
        var windowHeight = window.innerHeight;
        if (rect.top < windowHeight * 0.85) {
            el.classList.add('animated');
        }
    });
}

window.addEventListener('scroll', handleScrollAnimations);
window.addEventListener('load', handleScrollAnimations);

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(message) {
    var container = document.getElementById('toastContainer');
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = '<span class="toast-icon"><i class="fas fa-check-circle"></i></span><span class="toast-message">' + message + '</span>';
    container.appendChild(toast);

    setTimeout(function() {
        toast.classList.add('removing');
        setTimeout(function() {
            toast.remove();
        }, 400);
    }, 3000);
}

// ============================================
// ADD TO CART
// ============================================
function getCart() {
  return JSON.parse(localStorage.getItem("cart")) || [];
}

function saveCart(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
  cartItemCount = cart.length;
  var badge = document.getElementById("cartCount");
  if (badge) badge.textContent = cartItemCount;
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, function(c) {
    return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
  });
}

function addToCart(productName) {
  var cart = getCart();
  cart.push(productName);
  saveCart(cart);
  showToast('"' + productName + '" ajouté au panier ! 🛍️');
  renderCart();
}

// Affiche le contenu du panier (regroupé par produit + bouton retirer)
function renderCart() {
  var cart = getCart();
  var cartItems = document.getElementById("cartItems");
  if (!cartItems) return;
  cartItems.innerHTML = "";

  if (cart.length === 0) {
    cartItems.innerHTML = '<p class="cart-empty">Votre panier est vide</p>';
    return;
  }

  var groups = {};
  cart.forEach(function(item) { groups[item] = (groups[item] || 0) + 1; });

  Object.keys(groups).forEach(function(name) {
    var div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML =
      '<div class="cart-item-info"><h3>' + escapeHtml(name) + '</h3>' +
      '<p>Quantité : ' + groups[name] + '</p></div>' +
      '<button class="cart-item-remove" title="Retirer">&times;</button>';
    div.querySelector(".cart-item-remove").addEventListener("click", function() {
      removeFromCart(name);
    });
    cartItems.appendChild(div);
  });
}

// Retire toutes les occurrences d'un produit
function removeFromCart(name) {
  var cart = getCart().filter(function(item) { return item !== name; });
  saveCart(cart);
  renderCart();
}

// Vide entièrement le panier
function clearCart() {
  saveCart([]);
  renderCart();
}

function showCart() {
  renderCart();
  document.getElementById("cartDrawer").classList.add("active");
}

function closeCart() {
  document.getElementById("cartDrawer").classList.remove("active");
}

function goToCommande() {
  var cart = getCart();
  if (cart.length === 0) {
    showToast('Votre panier est vide 🛒');
    return;
  }
  window.location.href = "commande.php?parfum=" + encodeURIComponent(cart.join(", "));
}

// Initialise le compteur au chargement (le panier peut déjà contenir des articles)
document.addEventListener("DOMContentLoaded", function() {
  saveCart(getCart());
});


function addToCartFromModal() {
    var name = document.getElementById('modalName').textContent;
    addToCart(name);
    closeModal();
}

// ============================================
// WISHLIST TOGGLE
// ============================================
function toggleWishlist(btn) {
    btn.classList.toggle('active');
    var icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
        icon.className = 'fas fa-heart';
        showToast('Ajouté aux favoris ♥');
    } else {
        icon.className = 'far fa-heart';
        showToast('Retiré des favoris');
    }
}

// ============================================
// SIZE SELECTION (30ml & 50ml only)
// ============================================
function selectSize(btn) {
    var allSizes = document.querySelectorAll('.modal-size-btn');
    allSizes.forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');
}

// ============================================
// PRODUCT MODAL
// ============================================
function openModal(index) {
    var product = productsData[index];
    if (!product) return;

    document.getElementById('modalImage').src = product.image;
    document.getElementById('modalImage').alt = product.name;
    document.getElementById('modalCategory').textContent = product.category;
    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalPrice').textContent = product.price;
    document.getElementById('modalDesc').textContent = product.desc;
    document.getElementById('modalReviewCount').textContent = '(' + product.reviews + ')';

    var notesContainer = document.getElementById('modalNotes');
    notesContainer.innerHTML = '';
    product.notes.forEach(function(note) {
        var tag = document.createElement('span');
        tag.className = 'modal-note-tag';
        tag.textContent = note;
        notesContainer.appendChild(tag);
    });

    document.getElementById('modalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Keyboard close modal & sidebar
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeSidebar();
    }
});

// ============================================
// NEWSLETTER
// ============================================
function handleNewsletter(e) {
    e.preventDefault();
    var input = e.target.querySelector('.newsletter-input');
    showToast('Merci ! Vous êtes inscrit à notre newsletter ✨');
    input.value = '';
}

// ============================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// ============================================
var anchorLinks = document.querySelectorAll('a[href^="#"]');
anchorLinks.forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
        var targetId = this.getAttribute('href');
        if (targetId === '#') return;
        var target = document.querySelector(targetId);
        if (target) {
            e.preventDefault();
            var navHeight = mainNav.offsetHeight;
            var targetPosition = target.getBoundingClientRect().top + window.scrollY - navHeight;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }
    });
});


              </script>
                        </body>
                        </html>
                    