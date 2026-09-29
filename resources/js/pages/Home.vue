<template>
    <div class="home-page">

        <!-- ================= HEADER ================= -->
        <header class="header">
            <div class="container header-inner">

                <div class="logo">
                    Brand<span>X</span>
                </div>

                <nav class="nav">
                    <a href="#home">Home</a>
                    <a href="#about">About</a>
                    <a href="#marketplace">Marketplace</a>
                    <a href="#contact">Contact</a>
                </nav>

                <div class="auth-buttons">
                    <template v-if="isAuthenticated">
                        <router-link
                            :to="userDashboardRoute"
                            class="login-btn"
                        >
                            Dashboard
                        </router-link>
                    </template>

                    <template v-else>
                        <router-link to="/login" class="login-btn">
                            Login
                        </router-link>

                        <router-link to="/register" class="register-btn">
                            Register
                        </router-link>
                    </template>
                </div>

            </div>
        </header>


        <!-- ================= HERO ================= -->
        <section id="home" class="hero">
            <div class="container hero-content">

                <div class="hero-text">
                    <span class="badge">
                        Smart Marketplace Search
                    </span>

                    <h1>
                        Find Products From
                        <span>Multiple Marketplaces</span>
                    </h1>

                    <p>
                        Search products from different marketplaces
                        quickly and easily with BrandX.
                    </p>

                    <a href="#marketplace" class="hero-btn">
                        Start Searching
                    </a>
                </div>

            </div>
        </section>


        <!-- ================= ABOUT ================= -->
        <section id="about" class="about-section">
            <div class="container">

                <div class="section-heading">
                    <span>ABOUT US</span>
                    <h2>Everything You Need In One Place</h2>
                    <p>
                        BrandX helps you search for products across
                        multiple marketplaces from one simple interface.
                    </p>
                </div>

                <div class="about-grid">

                    <div class="about-card">
                        <div class="icon">🔎</div>
                        <h3>Easy Search</h3>
                        <p>
                            Enter your product and search when you are ready.
                        </p>
                    </div>

                    <div class="about-card">
                        <div class="icon">🛒</div>
                        <h3>Multiple Marketplaces</h3>
                        <p>
                            Compare products from different marketplaces.
                        </p>
                    </div>

                    <div class="about-card">
                        <div class="icon">⚡</div>
                        <h3>Fast Results</h3>
                        <p>
                            Get marketplace results in one convenient place.
                        </p>
                    </div>

                </div>

            </div>
        </section>


        <!-- ================= MARKETPLACE ================= -->
        <section id="marketplace" class="marketplace-section">
            <div class="container">

                <div class="section-heading">
                    <span>MARKETPLACE</span>

                    <h2>
                        Search Products
                    </h2>

                    <p>
                        Enter up to 10 products and click Search.
                        Products will not be searched while you are typing.
                    </p>
                </div>


                <!-- PRODUCT INPUTS -->
                <div class="product-search-box">

                    <div
                        v-for="(product, index) in productInputs"
                        :key="index"
                        class="product-input-row"
                    >

                        <div class="input-number">
                            {{ index + 1 }}
                        </div>

                        <input
                            v-model="productInputs[index]"
                            type="text"
                            :placeholder="`Enter Product ${index + 1}`"
                            @keyup.enter="searchProducts"
                        />

                    </div>


                    <!-- SEARCH BUTTON -->
                    <div class="search-button-wrapper">

                        <button
                            type="button"
                            class="search-btn"
                            @click="searchProducts"
                            :disabled="isSearching"
                        >
                            <span v-if="!isSearching">
                                🔎 Search Products
                            </span>

                            <span v-else>
                                ⏳ Searching...
                            </span>
                        </button>

                    </div>

                </div>


                <!-- ================= SEARCH RESULTS ================= -->
                <div
                    v-if="hasSearched"
                    class="results-section"
                >

                    <div
                        v-for="(category, index) in categories"
                        :key="category.key"
                        class="category-result"
                    >

                        <div class="category-header">

                            <div>
                                <span class="category-number">
                                    {{ index + 1 }}
                                </span>

                                <h3>
                                    {{ category.query }}
                                </h3>
                            </div>

                            <span
                                v-if="loadingStates[category.key]"
                                class="status loading"
                            >
                                Searching...
                            </span>

                            <span
                                v-else-if="errors[category.key]"
                                class="status failed"
                            >
                                Failed
                            </span>

                            <span
                                v-else
                                class="status completed"
                            >
                                Completed
                            </span>

                        </div>


                        <!-- ERROR -->
                        <div
                            v-if="errors[category.key]"
                            class="error-message"
                        >
                            {{ errors[category.key] }}
                        </div>


                        <!-- LOADING -->
                        <div
                            v-else-if="loadingStates[category.key]"
                            class="loading-box"
                        >
                            <div class="loader"></div>

                            <p>
                                Fetching products for
                                <strong>{{ category.query }}</strong>...
                            </p>
                        </div>


                        <!-- PRODUCTS -->
                        <div
                            v-else-if="category.products.length"
                            class="products-grid"
                        >

                            <div
                                v-for="(product, productIndex) in category.products"
                                :key="productIndex"
                                class="product-card"
                            >

                                <div class="product-image-wrapper">

                                    <img
                                        :src="getProductImage(product)"
                                        :alt="getProductName(product)"
                                        class="product-image"
                                        @error="handleImageError"
                                    />

                                </div>

                                <div class="product-info">

                                    <h4>
                                        {{ getProductName(product) }}
                                    </h4>

                                    <p
                                        v-if="getProductSeller(product)"
                                        class="seller"
                                    >
                                        Seller:
                                        {{ getProductSeller(product) }}
                                    </p>

                                    <div class="product-meta">

                                        <span
                                            v-if="getProductPrice(product)"
                                            class="price"
                                        >
                                            {{ getProductPrice(product) }}
                                        </span>

                                        <span
                                            v-if="getProductRating(product)"
                                            class="rating"
                                        >
                                            ⭐ {{ getProductRating(product) }}
                                        </span>

                                    </div>

                                    <a
                                        v-if="getProductUrl(product)"
                                        :href="getProductUrl(product)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="view-product"
                                    >
                                        View Product →
                                    </a>

                                </div>

                            </div>

                        </div>


                        <!-- NO RESULTS -->
                        <div
                            v-else
                            class="no-results"
                        >
                            No products found for
                            <strong>{{ category.query }}</strong>.
                        </div>

                    </div>

                </div>

            </div>
        </section>


        <!-- ================= FEATURES ================= -->
        <section class="features-section">
            <div class="container">

                <div class="section-heading">
                    <span>FEATURES</span>
                    <h2>Why Choose BrandX?</h2>
                </div>

                <div class="features-grid">

                    <div class="feature-card">
                        <div class="feature-icon">🌐</div>
                        <h3>Multiple Sources</h3>
                        <p>
                            Search products from multiple marketplaces.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Product Information</h3>
                        <p>
                            See product names, prices, sellers and ratings.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🚀</div>
                        <h3>Simple & Fast</h3>
                        <p>
                            One search interface for your product research.
                        </p>
                    </div>

                </div>

            </div>
        </section>


        <!-- ================= CTA ================= -->
        <section class="cta-section">
            <div class="container">

                <h2>
                    Ready To Find Your Products?
                </h2>

                <p>
                    Enter your products and click the Search button.
                </p>

                <a href="#marketplace" class="cta-btn">
                    Search Now
                </a>

            </div>
        </section>


        <!-- ================= FOOTER ================= -->
        <footer id="contact" class="footer">
            <div class="container footer-grid">

                <div>
                    <div class="footer-logo">
                        Brand<span>X</span>
                    </div>

                    <p>
                        Smart marketplace product searching made simple.
                    </p>
                </div>

                <div>
                    <h3>Quick Links</h3>

                    <a href="#home">Home</a>
                    <a href="#about">About</a>
                    <a href="#marketplace">Marketplace</a>
                </div>

                <div>
                    <h3>Contact</h3>

                    <p>📧 support@brandx.com</p>
                    <p>📞 +92 300 0000000</p>
                </div>

                <div>
                    <h3>Social</h3>

                    <div class="social-links">
                        <a href="#" @click.prevent>Facebook</a>
                        <a href="#" @click.prevent>Instagram</a>
                        <a href="#" @click.prevent>YouTube</a>
                    </div>
                </div>

            </div>

            <div class="footer-bottom">
                © {{ currentYear }} BrandX. All Rights Reserved.
            </div>
        </footer>


        <!-- ================= GUEST FLOATING CHAT ================= -->
        <transition name="chat-pop">
            <button
                v-if="!isAuthenticated"
                class="guest-chat-button"
                @click="openAuthModal"
                aria-label="Chat with support"
                type="button"
            >
                <!-- Chat bubble icon -->
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="26"
                    height="26"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                    <line x1="8" y1="14" x2="13" y2="14"/>
                </svg>

                <span class="guest-chat-tooltip">
                    Chat with support
                </span>

                <span class="guest-chat-pulse"></span>
            </button>
        </transition>


        <!-- ================= AUTH MODAL ================= -->
        <transition name="modal-fade">
            <div
                v-if="showAuthModal"
                class="modal-overlay"
                @click.self="closeAuthModal"
            >
                <div class="auth-modal">

                    <button
                        class="modal-close"
                        @click="closeAuthModal"
                        aria-label="Close"
                        type="button"
                    >
                        ×
                    </button>

                    <div class="modal-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="30"
                            height="30"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            <line x1="8" y1="10" x2="16" y2="10"/>
                            <line x1="8" y1="14" x2="13" y2="14"/>
                        </svg>
                    </div>

                    <h2>Chat with Support</h2>

                    <p class="modal-subtitle">
                        Please login or create an account to start
                        chatting with our support team.
                    </p>

                    <div class="modal-actions">
                        <router-link
                            to="/login"
                            class="modal-btn modal-btn-primary"
                        >
                            Login
                        </router-link>

                        <router-link
                            to="/register"
                            class="modal-btn modal-btn-secondary"
                        >
                            Register
                        </router-link>
                    </div>

                    <p class="modal-footnote">
                        Live chat is available after login.
                    </p>

                </div>
            </div>
        </transition>

    </div>
</template>


<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

import { authState } from '../stores/auth';


/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

const API_BASE = 'http://127.0.0.1:8000';


/*
|--------------------------------------------------------------------------
| Auth State
|--------------------------------------------------------------------------
*/

const isAuthenticated = computed(() => {
    return !!authState.user;
});

const userDashboardRoute = computed(() => {
    return authState.user?.role === 'admin'
        ? '/admin/dashboard'
        : '/dashboard';
});


/*
|--------------------------------------------------------------------------
| Auth Modal
|--------------------------------------------------------------------------
*/

const showAuthModal = ref(false);

function openAuthModal() {
    showAuthModal.value = true;
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    showAuthModal.value = false;
    document.body.style.overflow = '';
}


/*
|--------------------------------------------------------------------------
| Marketplace Inputs
|--------------------------------------------------------------------------
*/

const productInputs = ref(
    Array(10).fill('')
);


/*
|--------------------------------------------------------------------------
| Search State
|--------------------------------------------------------------------------
*/

const isSearching = ref(false);
const hasSearched = ref(false);

const categories = ref([]);

const loadingStates = ref({});
const errors = ref({});


/*
|--------------------------------------------------------------------------
| Placeholder Image
|--------------------------------------------------------------------------
*/

const placeholderImage =
    'https://via.placeholder.com/300x220?text=No+Image';


/*
|--------------------------------------------------------------------------
| Current Year
|--------------------------------------------------------------------------
*/

const currentYear = new Date().getFullYear();


/*
|--------------------------------------------------------------------------
| Search Products
|--------------------------------------------------------------------------
*/

const searchProducts = async () => {

    if (isSearching.value) {
        return;
    }

    const searches = productInputs.value
        .map(value => String(value || '').trim())
        .filter(value => value.length > 0);

    if (!searches.length) {
        alert('Please enter at least one product.');
        return;
    }

    categories.value = [];
    loadingStates.value = {};
    errors.value = {};
    hasSearched.value = true;
    isSearching.value = true;

    for (const query of searches) {

        const key =
            `${query}-${Date.now()}-${Math.random()}`;

        categories.value.push({
            key,
            query,
            products: []
        });

        loadingStates.value[key] = true;
        errors.value[key] = null;

        try {

            const searchId = await startSearch(query);

            if (!searchId) {
                throw new Error(
                    'Search ID was not returned by the server.'
                );
            }

            const products = await pollSearch(searchId);

            const category = categories.value.find(
                item => item.key === key
            );

            if (category) {
                category.products = normalizeProducts(products);
            }

        } catch (error) {

            console.error(
                `Marketplace search failed for "${query}":`,
                error
            );

            errors.value[key] = getErrorMessage(error);

        } finally {
            loadingStates.value[key] = false;
        }
    }

    isSearching.value = false;
};


/*
|--------------------------------------------------------------------------
| Start Search
|--------------------------------------------------------------------------
*/

const startSearch = async (query) => {
    try {
        const response = await axios.post(
            `${API_BASE}/api/marketplace/search`,
            { query },
            {
                withCredentials: true,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                }
            }
        );

        console.log('Marketplace search response:', response.data);

        const data = response.data;

        return (
            data?.id ??
            data?.search_id ??
            data?.search?.id ??
            data?.data?.id ??
            data?.data?.search_id ??
            data?.data?.search?.id ??
            null
        );

    } catch (error) {
        console.error('Start marketplace search error:', error);
        throw error;
    }
};


/*
|--------------------------------------------------------------------------
| Poll Search
|--------------------------------------------------------------------------
*/

const pollSearch = async (searchId) => {

    const maxAttempts = 90;

    for (let attempt = 0; attempt < maxAttempts; attempt++) {

        try {

            const response = await axios.get(
                `${API_BASE}/api/marketplace/status/${searchId}`,
                {
                    withCredentials: true,
                    headers: { Accept: 'application/json' }
                }
            );

            const data = response.data;

            console.log(`Search status ${searchId}:`, data);

            const status = String(
                data?.status ??
                data?.data?.status ??
                data?.search?.status ??
                ''
            ).toLowerCase();

            if (
                status === 'completed' ||
                status === 'complete' ||
                status === 'done' ||
                status === 'success' ||
                status === 'successful'
            ) {
                return (
                    data?.products ??
                    data?.results ??
                    data?.data?.products ??
                    data?.data?.results ??
                    data?.search?.products ??
                    data?.data ??
                    []
                );
            }

            if (status === 'failed' || status === 'error') {
                throw new Error(
                    data?.message ??
                    data?.error ??
                    'Marketplace search failed.'
                );
            }

        } catch (error) {

            if (error?.response?.status === 404) {
                throw new Error('Search record was not found.');
            }

            throw error;
        }

        await sleep(2000);
    }

    throw new Error('Search timed out. Please try again.');
};


/*
|--------------------------------------------------------------------------
| Sleep
|--------------------------------------------------------------------------
*/

const sleep = (milliseconds) => {
    return new Promise(
        resolve => setTimeout(resolve, milliseconds)
    );
};


/*
|--------------------------------------------------------------------------
| Normalize Products
|--------------------------------------------------------------------------
*/

const normalizeProducts = (data) => {

    if (!data) return [];

    if (Array.isArray(data)) return data;

    if (Array.isArray(data.products)) return data.products;
    if (Array.isArray(data.results)) return data.results;
    if (Array.isArray(data.data)) return data.data;
    if (Array.isArray(data?.data?.products)) return data.data.products;
    if (Array.isArray(data?.data?.results)) return data.data.results;
    if (Array.isArray(data?.data?.data)) return data.data.data;
    if (Array.isArray(data.marketplaces)) return data.marketplaces;

    return [];
};


/*
|--------------------------------------------------------------------------
| Product Helpers
|--------------------------------------------------------------------------
*/

const getProductName = (product) => {
    return (
        product?.name ??
        product?.title ??
        product?.product_name ??
        product?.productTitle ??
        'Product'
    );
};

const getProductImage = (product) => {
    return (
        product?.image ??
        product?.image_url ??
        product?.thumbnail ??
        product?.thumbnail_url ??
        product?.imageUrl ??
        placeholderImage
    );
};

const getProductPrice = (product) => {
    return (
        product?.price ??
        product?.current_price ??
        product?.sale_price ??
        product?.product_price ??
        ''
    );
};

const getProductSeller = (product) => {
    return (
        product?.seller ??
        product?.store ??
        product?.shop ??
        product?.marketplace ??
        ''
    );
};

const getProductRating = (product) => {
    return (
        product?.rating ??
        product?.ratings ??
        product?.review_rating ??
        ''
    );
};

const getProductUrl = (product) => {
    return (
        product?.url ??
        product?.product_url ??
        product?.link ??
        product?.product_link ??
        ''
    );
};

const handleImageError = (event) => {
    event.target.src = placeholderImage;
};


/*
|--------------------------------------------------------------------------
| Error Message
|--------------------------------------------------------------------------
*/

const getErrorMessage = (error) => {

    const status = error?.response?.status;

    if (status === 401) return 'Authentication required.';
    if (status === 419) return 'Session or CSRF token expired.';
    if (status === 404) return 'Marketplace search route was not found.';

    if (status === 422) {
        return (
            error?.response?.data?.message ??
            'Invalid search request.'
        );
    }

    if (status === 500) {
        return (
            error?.response?.data?.message ??
            'Server error occurred.'
        );
    }

    return (
        error?.response?.data?.message ??
        error?.message ??
        'Unable to search marketplace.'
    );
};
</script>


<style scoped>

* {
    box-sizing: border-box;
}

.home-page {
    min-height: 100vh;
    background: #ffffff;
    color: #1f2937;
}

.container {
    width: min(1200px, 92%);
    margin: 0 auto;
}


/* ================= HEADER ================= */

.header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.header-inner {
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
}

.logo,
.footer-logo {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
}

.logo span,
.footer-logo span {
    color: #2563eb;
}

.nav {
    display: flex;
    align-items: center;
    gap: 28px;
}

.nav a {
    color: #374151;
    text-decoration: none;
    font-weight: 500;
}

.nav a:hover {
    color: #2563eb;
}

.auth-buttons {
    display: flex;
    gap: 10px;
}

.login-btn,
.register-btn {
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
}

.login-btn {
    color: #2563eb;
    border: 1px solid #2563eb;
}

.register-btn {
    background: #2563eb;
    color: white;
}


/* ================= HERO ================= */

.hero {
    padding: 100px 0;
    background: linear-gradient(135deg, #eff6ff, #ffffff);
}

.hero-content {
    display: flex;
    align-items: center;
    min-height: 420px;
}

.hero-text {
    max-width: 720px;
}

.badge {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 14px;
    font-weight: 700;
}

.hero h1 {
    margin: 20px 0;
    font-size: clamp(40px, 6vw, 68px);
    line-height: 1.08;
}

.hero h1 span {
    display: block;
    color: #2563eb;
}

.hero p {
    max-width: 650px;
    font-size: 18px;
    line-height: 1.8;
    color: #6b7280;
}

.hero-btn,
.cta-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 13px 24px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 700;
}


/* ================= SECTIONS ================= */

.about-section,
.marketplace-section,
.features-section {
    padding: 90px 0;
}

.section-heading {
    text-align: center;
    max-width: 750px;
    margin: 0 auto 45px;
}

.section-heading > span {
    color: #2563eb;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 1px;
}

.section-heading h2 {
    margin: 10px 0;
    font-size: 38px;
}

.section-heading p {
    color: #6b7280;
    line-height: 1.7;
}


/* ================= ABOUT ================= */

.about-grid,
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.about-card,
.feature-card {
    padding: 30px;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: white;
    text-align: center;
}

.icon,
.feature-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.about-card h3,
.feature-card h3 {
    margin-bottom: 10px;
}

.about-card p,
.feature-card p {
    color: #6b7280;
    line-height: 1.7;
}


/* ================= MARKETPLACE ================= */

.marketplace-section {
    background: #f8fafc;
}

.product-search-box {
    max-width: 850px;
    margin: 0 auto;
    padding: 28px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
}

.product-input-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.input-number {
    min-width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #dbeafe;
    color: #2563eb;
    font-weight: 800;
}

.product-input-row input {
    width: 100%;
    height: 45px;
    padding: 0 15px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    outline: none;
    font-size: 15px;
}

.product-input-row input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-button-wrapper {
    margin-top: 22px;
    text-align: center;
}

.search-btn {
    border: none;
    padding: 14px 30px;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.search-btn:hover:not(:disabled) {
    background: #1d4ed8;
}

.search-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}


/* ================= RESULTS ================= */

.results-section {
    margin-top: 50px;
}

.category-result {
    margin-bottom: 35px;
    padding: 25px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
}

.category-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.category-header > div {
    display: flex;
    align-items: center;
    gap: 12px;
}

.category-header h3 {
    margin: 0;
    font-size: 22px;
}

.category-number {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #2563eb;
    color: white;
    font-weight: 700;
}

.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.status.loading {
    background: #fef3c7;
    color: #92400e;
}

.status.completed {
    background: #dcfce7;
    color: #166534;
}

.status.failed {
    background: #fee2e2;
    color: #991b1b;
}


/* ================= LOADING ================= */

.loading-box {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.loader {
    width: 35px;
    height: 35px;
    margin: 0 auto 15px;
    border: 4px solid #dbeafe;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}


/* ================= PRODUCTS ================= */

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.product-card {
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: white;
}

.product-image-wrapper {
    height: 190px;
    background: #f8fafc;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-info {
    padding: 15px;
}

.product-info h4 {
    margin: 0 0 10px;
    font-size: 15px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.seller {
    margin: 0 0 8px;
    color: #6b7280;
    font-size: 13px;
}

.product-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 12px;
}

.price {
    color: #2563eb;
    font-weight: 800;
}

.rating {
    font-size: 13px;
}

.view-product {
    display: block;
    text-align: center;
    padding: 9px;
    background: #eff6ff;
    color: #2563eb;
    text-decoration: none;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 700;
}

.no-results,
.error-message {
    padding: 20px;
    text-align: center;
    border-radius: 10px;
}

.no-results {
    background: #f9fafb;
    color: #6b7280;
}

.error-message {
    background: #fef2f2;
    color: #b91c1c;
}


/* ================= CTA ================= */

.cta-section {
    padding: 80px 20px;
    text-align: center;
    background: #eff6ff;
}

.cta-section h2 {
    font-size: 38px;
    margin-bottom: 10px;
}

.cta-section p {
    color: #6b7280;
}


/* ================= FOOTER ================= */

.footer {
    padding-top: 60px;
    background: #111827;
    color: white;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    padding-bottom: 50px;
}

.footer-logo {
    color: white;
    margin-bottom: 15px;
}

.footer p {
    color: #9ca3af;
    line-height: 1.7;
}

.footer h3 {
    margin-top: 0;
}

.footer a {
    display: block;
    margin-bottom: 10px;
    color: #9ca3af;
    text-decoration: none;
}

.footer a:hover {
    color: white;
}

.footer-bottom {
    padding: 20px;
    text-align: center;
    border-top: 1px solid #374151;
    color: #9ca3af;
}


/* =========================================================
   GUEST FLOATING CHAT BUTTON
========================================================= */

.guest-chat-button {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 9998;

    width: 60px;
    height: 60px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: none;
    border-radius: 50%;

    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;

    cursor: pointer;

    box-shadow:
        0 8px 24px rgba(99, 102, 241, 0.35),
        0 0 0 0 rgba(99, 102, 241, 0.4);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.guest-chat-button:hover {
    transform: scale(1.06);

    box-shadow:
        0 12px 30px rgba(99, 102, 241, 0.45),
        0 0 0 0 rgba(99, 102, 241, 0.4);
}

.guest-chat-button svg {
    display: block;
    width: 26px;
    height: 26px;
    pointer-events: none;
    z-index: 2;
}

.guest-chat-tooltip {
    position: absolute;
    right: 74px;
    top: 50%;
    transform: translateY(-50%);

    padding: 8px 14px;

    background: #111827;
    color: #ffffff;

    font-size: 12px;
    font-weight: 600;

    border-radius: 8px;

    white-space: nowrap;

    opacity: 0;
    pointer-events: none;

    transition: opacity 0.2s ease;
}

.guest-chat-tooltip::after {
    content: '';

    position: absolute;
    top: 50%;
    left: 100%;

    transform: translateY(-50%);

    border: 6px solid transparent;
    border-left-color: #111827;
}

.guest-chat-button:hover .guest-chat-tooltip {
    opacity: 1;
}

/* Pulse ring */
.guest-chat-pulse {
    position: absolute;
    inset: 0;

    border-radius: 50%;

    background: rgba(99, 102, 241, 0.5);

    animation: guest-pulse 2s infinite;

    z-index: 1;

    pointer-events: none;
}

@keyframes guest-pulse {
    0% {
        transform: scale(1);
        opacity: 0.6;
    }

    70% {
        transform: scale(1.4);
        opacity: 0;
    }

    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}

/* Pop-in transition */
.chat-pop-enter-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.chat-pop-leave-active {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.chat-pop-enter-from {
    transform: scale(0.5);
    opacity: 0;
}

.chat-pop-leave-to {
    transform: scale(0.5);
    opacity: 0;
}


/* =========================================================
   AUTH MODAL
========================================================= */

.modal-overlay {
    position: fixed;
    inset: 0;

    z-index: 10000;

    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;
}

.auth-modal {
    position: relative;

    width: 100%;
    max-width: 420px;

    padding: 36px 32px;

    background: #ffffff;

    border-radius: 20px;

    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.25);

    text-align: center;
}

.modal-close {
    position: absolute;
    top: 14px;
    right: 14px;

    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: none;
    border-radius: 50%;

    background: #f3f4f6;
    color: #6b7280;

    font-size: 22px;
    line-height: 1;

    cursor: pointer;

    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.modal-close:hover {
    background: #e5e7eb;
    color: #111827;
}

.modal-icon {
    width: 72px;
    height: 72px;

    margin: 0 auto 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: linear-gradient(135deg, #eef2ff, #e0f2fe);
    color: #4f46e5;
}

.modal-icon svg {
    display: block;
}

.auth-modal h2 {
    margin: 0 0 10px;

    color: #111827;

    font-size: 23px;
    font-weight: 700;
}

.modal-subtitle {
    margin: 0 0 28px;

    color: #6b7280;

    font-size: 14px;
    line-height: 1.6;
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;

    margin-bottom: 20px;
}

.modal-btn {
    display: flex;
    align-items: center;
    justify-content: center;

    height: 48px;

    border-radius: 10px;

    font-size: 15px;
    font-weight: 600;

    text-decoration: none;

    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        background 0.2s ease;
}

.modal-btn-primary {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;

    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.28);
}

.modal-btn-primary:hover {
    transform: translateY(-1px);

    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.modal-btn-secondary {
    background: #f3f4f6;
    color: #111827;
}

.modal-btn-secondary:hover {
    background: #e5e7eb;
}

.modal-footnote {
    margin: 0;

    color: #9ca3af;

    font-size: 12px;
}

/* Modal fade transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}


/* ================= RESPONSIVE ================= */

@media (max-width: 1000px) {

    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .about-grid,
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}


@media (max-width: 700px) {

    .header-inner {
        flex-wrap: wrap;
        padding: 15px 0;
    }

    .nav {
        order: 3;
        width: 100%;
        justify-content: center;
        gap: 15px;
    }

    .nav a {
        font-size: 13px;
    }

    .hero {
        padding: 60px 0;
    }

    .hero h1 {
        font-size: 42px;
    }

    .about-grid,
    .features-grid,
    .products-grid {
        grid-template-columns: 1fr;
    }

    .footer-grid {
        grid-template-columns: 1fr;
    }

    .category-header {
        align-items: flex-start;
        flex-direction: column;
    }
}


@media (max-width: 500px) {

    .guest-chat-button {
        width: 54px;
        height: 54px;
        right: 16px;
        bottom: 16px;
    }

    .guest-chat-button svg {
        width: 22px;
        height: 22px;
    }

    .guest-chat-tooltip {
        display: none;
    }

    .auth-modal {
        padding: 30px 22px;
    }

    .auth-modal h2 {
        font-size: 20px;
    }
}
</style>