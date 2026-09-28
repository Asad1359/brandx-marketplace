<template>
    <div class="marketplace-page">

        <!-- =========================================================
             HEADER
        ========================================================== -->

        <div class="page-header">
            <h1>Search Products</h1>
            <p>Enter one or more product names</p>
        </div>


        <!-- =========================================================
             SEARCH INPUTS
        ========================================================== -->

        <div class="search-wrapper">

            <div class="input-grid">

                <div
                    v-for="(product, index) in productInputs"
                    :key="index"
                    class="input-item"
                >

                    <label :for="`product-${index}`">
                        {{ index + 1 }} Product {{ index + 1 }}
                    </label>

                    <input
                        :id="`product-${index}`"
                        v-model="productInputs[index]"
                        type="text"
                        class="form-control"
                        :placeholder="`Enter product ${index + 1}`"
                        @keyup.enter="searchProducts"
                    />

                </div>

            </div>


            <!-- =====================================================
                 SEARCH BUTTON
            ====================================================== -->

            <div class="button-wrapper">

                <button
                    type="button"
                    class="search-btn"
                    :disabled="loading"
                    @click="searchProducts"
                >

                    <span v-if="loading">
                        Searching...
                    </span>

                    <span v-else>
                        Search Products
                    </span>

                </button>

            </div>

        </div>


        <!-- =========================================================
             ERROR
        ========================================================== -->

        <div
            v-if="errorMessage"
            class="alert alert-danger mt-4"
        >
            {{ errorMessage }}
        </div>


        <!-- =========================================================
             RESULTS
        ========================================================== -->

        <div
            v-if="categories.length"
            class="results-wrapper"
        >

            <div
                v-for="category in categories"
                :key="category.search_id"
                class="category-section"
            >

                <!-- =================================================
                     CATEGORY HEADER
                ================================================== -->

                <div class="category-header">

                    <h2>
                        Results for:
                        <strong>{{ category.search }}</strong>
                    </h2>

                    <span
                        v-if="category.loading"
                        class="status"
                    >
                        Fetching products...
                    </span>

                    <span
                        v-else-if="category.status === 'failed'"
                        class="status status-error"
                    >
                        Search failed
                    </span>

                    <span
                        v-else
                        class="status"
                    >
                        {{ category.products.length }} Products
                    </span>

                </div>


                <!-- =================================================
                     LOADING
                ================================================== -->

                <div
                    v-if="category.loading"
                    class="loading-box"
                >

                    <div class="spinner"></div>

                    <p>
                        Fetching live marketplace products...
                    </p>

                </div>


                <!-- =================================================
                     PRODUCTS
                ================================================== -->

                <div
                    v-else-if="category.products.length"
                    class="products-grid"
                >

                    <div
                        v-for="(product, productIndex) in category.products"
                        :key="productIndex"
                        class="product-card"
                    >

                        <!-- Product Image -->

                        <div class="product-image-wrapper">

                            <img
                                :src="product.image || placeholderImage"
                                :alt="product.name || 'Product'"
                                class="product-image"
                                @error="handleImageError"
                            />

                        </div>


                        <!-- Product Body -->

                        <div class="product-body">

                            <!-- Seller -->

                            <div
                                v-if="product.seller"
                                class="seller"
                            >
                                {{ product.seller }}
                            </div>

                            <div
                                v-else
                                class="seller seller-empty"
                            >
                                &nbsp;
                            </div>


                            <!-- Product Name -->

                            <h3 class="product-name">
                                {{ product.name || 'Product name unavailable' }}
                            </h3>


                            <!-- Rating -->

                            <div
                                v-if="product.rating || product.reviews"
                                class="rating"
                            >

                                <span v-if="product.rating">
                                    ⭐ {{ product.rating }}
                                </span>

                                <span v-if="product.rating && product.reviews">
                                    &nbsp;
                                </span>

                                <span v-if="product.reviews">
                                    ({{ product.reviews }} reviews)
                                </span>

                            </div>

                            <div
                                v-else
                                class="rating rating-empty"
                            >
                                &nbsp;
                            </div>


                            <!-- Footer -->

                            <div class="product-footer">

                                <span class="price">
                                    {{ product.price || 'Price unavailable' }}
                                </span>

                                <a
                                    v-if="product.url"
                                    :href="product.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="view-btn"
                                >
                                    View Product
                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     NO PRODUCTS / ERROR
                ================================================== -->

                <div
                    v-else
                    class="no-products"
                >

                    <template v-if="category.error">

                        {{ category.error }}

                    </template>

                    <template v-else>

                        No live products found for
                        <strong>{{ category.search }}</strong>.

                    </template>

                </div>

            </div>

        </div>

    </div>
</template>


<script setup>

import axios from 'axios';

import {
    ref,
    onBeforeUnmount
} from 'vue';


/*
|--------------------------------------------------------------------------
| AXIOS / SANCTUM CONFIGURATION
|--------------------------------------------------------------------------
*/

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;


/*
|--------------------------------------------------------------------------
| PRODUCT INPUTS
|--------------------------------------------------------------------------
*/

const productInputs = ref(
    Array(12).fill('')
);


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const categories = ref([]);

const loading = ref(false);

const errorMessage = ref('');


/*
|--------------------------------------------------------------------------
| PLACEHOLDER IMAGE
|--------------------------------------------------------------------------
*/

const placeholderImage =
    'https://via.placeholder.com/400x300?text=No+Image';


/*
|--------------------------------------------------------------------------
| SLEEP
|--------------------------------------------------------------------------
*/

const sleep = (ms) => {

    return new Promise(resolve => {

        setTimeout(resolve, ms);

    });

};


/*
|--------------------------------------------------------------------------
| GET SANCTUM CSRF COOKIE
|--------------------------------------------------------------------------
*/

const initializeSanctum = async () => {

    try {

        await axios.get(
            '/sanctum/csrf-cookie',
            {
                withCredentials: true,

                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

    } catch (error) {

        console.error(
            'Sanctum CSRF initialization failed:',
            error
        );

        throw new Error(
            'Unable to initialize authentication session. Please refresh the page and try again.'
        );

    }

};


/*
|--------------------------------------------------------------------------
| SEARCH PRODUCTS
|--------------------------------------------------------------------------
*/

const searchProducts = async () => {

    errorMessage.value = '';

    categories.value = [];


    const searches = productInputs.value
        .map(value => String(value || '').trim())
        .filter(value => value !== '');


    if (!searches.length) {

        errorMessage.value =
            'Please enter at least one product name.';

        return;

    }


    loading.value = true;


    try {

        await initializeSanctum();


        for (const search of searches) {

            await startSearch(search);

        }

    } catch (error) {

        console.error(
            'Marketplace search error:',
            error
        );

        errorMessage.value =
            getErrorMessage(error);

    } finally {

        loading.value = false;

    }

};


/*
|--------------------------------------------------------------------------
| START MARKETPLACE SEARCH
|--------------------------------------------------------------------------
| POST /api/marketplace/search
|--------------------------------------------------------------------------
*/

const startSearch = async (search) => {

    try {

        const response = await axios.post(

            '/api/marketplace/search',

            {
                query: search
            },

            {
                withCredentials: true,

                withXSRFToken: true,

                headers: {
                    Accept: 'application/json',

                    'X-Requested-With':
                        'XMLHttpRequest'
                }

            }

        );


        const data =
            response.data;


        if (!data.success) {

            throw new Error(

                data.message ||
                'Marketplace search failed.'

            );

        }


        const category = {

            search_id:
                data.search_id,

            search:
                data.search || search,

            status:
                data.status || 'pending',

            loading:
                true,

            products:
                [],

            error:
                null

        };


        categories.value.push(
            category
        );


        await pollSearch(
            category
        );

    } catch (error) {

        console.error(
            `Search failed for ${search}:`,
            error
        );


        categories.value.push({

            search_id:
                `failed-${Date.now()}-${Math.random()}`,

            search:
                search,

            status:
                'failed',

            loading:
                false,

            products:
                [],

            error:
                getErrorMessage(error)

        });

    }

};


/*
|--------------------------------------------------------------------------
| POLL MARKETPLACE RESULT
|--------------------------------------------------------------------------
| GET /api/marketplace/status/{id}
|--------------------------------------------------------------------------
*/

const pollSearch = async (category) => {

    const maxAttempts = 90;

    let attempts = 0;


    while (
        attempts < maxAttempts
    ) {

        attempts++;


        try {

            const response = await axios.get(

                `/api/marketplace/status/${category.search_id}`,

                {
                    withCredentials:
                        true,

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest'
                    }

                }

            );


            const data =
                response.data;


            category.status =
                data.status ||
                'pending';


            if (
                data.status ===
                'completed'
            ) {

                category.products =
                    Array.isArray(
                        data.products
                    )
                        ? data.products
                        : [];


                category.loading =
                    false;


                if (
                    category.products.length === 0
                ) {

                    category.error =
                        data.message ||
                        `No live products found for ${category.search}.`;

                } else {

                    category.error =
                        null;

                }


                return;

            }


            if (
                data.status ===
                'failed'
            ) {

                category.products =
                    [];

                category.loading =
                    false;

                category.error =
                    data.message ||
                    'Marketplace search failed.';

                return;

            }


            await sleep(2000);

        } catch (error) {

            console.error(
                'Polling error:',
                error
            );


            category.products =
                [];

            category.loading =
                false;

            category.error =
                getErrorMessage(error);

            return;

        }

    }


    category.loading =
        false;

    category.error =
        'Marketplace search timed out. Please try again.';

};


/*
|--------------------------------------------------------------------------
| ERROR MESSAGE
|--------------------------------------------------------------------------
*/

const getErrorMessage = (error) => {

    if (
        error?.response?.status === 401
    ) {

        return 'Unauthenticated. Please login again.';

    }


    if (
        error?.response?.status === 419
    ) {

        return 'Your session or CSRF token has expired. Please refresh the page and login again.';

    }


    if (
        error?.response?.data?.message
    ) {

        return error.response.data.message;

    }


    if (
        error?.message
    ) {

        return error.message;

    }


    return 'Unable to start marketplace search.';

};


/*
|--------------------------------------------------------------------------
| IMAGE ERROR
|--------------------------------------------------------------------------
*/

const handleImageError = (event) => {

    event.target.src =
        placeholderImage;

};


/*
|--------------------------------------------------------------------------
| CLEANUP
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {

    categories.value = [];

});

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| MAIN PAGE
|--------------------------------------------------------------------------
*/

.marketplace-page {
    width: 100%;
    max-width: 100%;

    margin: 0;
    padding: 0;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.page-header {
    width: 100%;

    margin: 0 0 25px 0;
    padding: 0;

    box-sizing: border-box;
}

.page-header h1 {
    margin: 0 0 8px 0;

    font-size: 32px;

    font-weight: 700;
}

.page-header p {
    margin: 0;

    color: #666;

    font-size: 16px;
}


/*
|--------------------------------------------------------------------------
| SEARCH WRAPPER
|--------------------------------------------------------------------------
*/

.search-wrapper {
    width: 100%;
    max-width: 100%;

    margin: 0;
    padding: 20px;

    background: #ffffff;

    border: 1px solid #e5e5e5;

    border-radius: 12px;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| INPUT GRID
|--------------------------------------------------------------------------
*/

.input-grid {
    width: 100%;
    max-width: 100%;

    margin: 0;
    padding: 0;

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 16px;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| INPUT ITEM
|--------------------------------------------------------------------------
*/

.input-item {
    width: 100%;
    min-width: 0;

    margin: 0;
    padding: 0;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| INPUT LABEL
|--------------------------------------------------------------------------
*/

.input-item label {
    display: block;

    width: 100%;

    margin: 0 0 7px 0;

    font-size: 14px;

    font-weight: 600;

    color: #333;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

.form-control {
    display: block;

    width: 100%;
    max-width: 100%;
    min-width: 0;

    min-height: 45px;

    margin: 0;
    padding: 10px 12px;

    border: 1px solid #ced4da;

    border-radius: 7px;

    background: #ffffff;

    color: #222;

    font-size: 14px;

    line-height: 1.5;

    outline: none;

    box-sizing: border-box;
}

.form-control:focus {
    border-color: #777;

    box-shadow:
        0 0 0 2px
        rgba(0, 0, 0, 0.08);
}

.form-control::placeholder {
    color: #999;
}


/*
|--------------------------------------------------------------------------
| BUTTON WRAPPER
|--------------------------------------------------------------------------
*/

.button-wrapper {
    width: 100%;

    margin-top: 20px;

    text-align: left;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| SEARCH BUTTON
|--------------------------------------------------------------------------
*/

.search-btn {
    border: none;

    padding: 11px 24px;

    border-radius: 7px;

    background: #222;

    color: #ffffff;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition:
        background 0.2s ease,
        opacity 0.2s ease;
}

.search-btn:hover {
    background: #000;
}

.search-btn:disabled {
    opacity: 0.6;

    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| RESULTS WRAPPER
|--------------------------------------------------------------------------
*/

.results-wrapper {
    width: 100%;
    max-width: 100%;

    margin-top: 30px;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| CATEGORY
|--------------------------------------------------------------------------
*/

.category-section {
    width: 100%;

    margin-bottom: 40px;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| CATEGORY HEADER
|--------------------------------------------------------------------------
*/

.category-header {
    width: 100%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 18px;

    box-sizing: border-box;
}

.category-header h2 {
    margin: 0;

    font-size: 22px;

    font-weight: 500;
}

.category-header h2 strong {
    font-weight: 700;
}

.status {
    font-size: 14px;

    color: #666;

    white-space: nowrap;
}

.status-error {
    color: #dc3545;
}


/*
|--------------------------------------------------------------------------
| LOADING
|--------------------------------------------------------------------------
*/

.loading-box {
    width: 100%;

    padding: 40px;

    text-align: center;

    background: #ffffff;

    border-radius: 10px;

    border: 1px solid #e5e5e5;

    box-sizing: border-box;
}

.loading-box p {
    margin: 15px 0 0;

    color: #666;
}


/*
|--------------------------------------------------------------------------
| SPINNER
|--------------------------------------------------------------------------
*/

.spinner {
    width: 35px;

    height: 35px;

    margin: auto;

    border:
        4px solid #ddd;

    border-top-color:
        #222;

    border-radius: 50%;

    animation:
        spin 0.8s linear infinite;
}

@keyframes spin {

    to {
        transform:
            rotate(360deg);
    }

}


/*
|--------------------------------------------------------------------------
| PRODUCTS GRID
|--------------------------------------------------------------------------
*/

.products-grid {
    width: 100%;
    max-width: 100%;

    margin: 0;
    padding: 0;

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 18px;

    align-items: stretch;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| PRODUCT CARD
|--------------------------------------------------------------------------
*/

.product-card {
    width: 100%;
    min-width: 0;

    height: 100%;

    margin: 0;
    padding: 0;

    background: #ffffff;

    border: 1px solid #e5e5e5;

    border-radius: 10px;

    overflow: hidden;

    display: flex;

    flex-direction: column;

    box-sizing: border-box;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.product-card:hover {
    transform:
        translateY(-3px);

    box-shadow:
        0 5px 18px
        rgba(0, 0, 0, 0.08);
}


/*
|--------------------------------------------------------------------------
| PRODUCT IMAGE
|--------------------------------------------------------------------------
*/

.product-image-wrapper {
    width: 100%;

    height: 200px;

    margin: 0;
    padding: 0;

    background: #f5f5f5;

    flex-shrink: 0;

    overflow: hidden;

    box-sizing: border-box;
}

.product-image {
    display: block;

    width: 100%;

    height: 100%;

    margin: 0;
    padding: 0;

    object-fit: contain;
}


/*
|--------------------------------------------------------------------------
| PRODUCT BODY
|--------------------------------------------------------------------------
*/

.product-body {
    width: 100%;
    min-width: 0;

    padding: 15px;

    display: flex;

    flex-direction: column;

    flex: 1;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| SELLER
|--------------------------------------------------------------------------
*/

.seller {
    width: 100%;
    min-width: 0;

    margin: 0 0 6px 0;

    color: #777;

    font-size: 12px;

    height: 18px;

    line-height: 18px;

    overflow: hidden;

    white-space: nowrap;

    text-overflow: ellipsis;

    flex-shrink: 0;
}

.seller-empty {
    visibility: hidden;
}


/*
|--------------------------------------------------------------------------
| PRODUCT NAME
|--------------------------------------------------------------------------
*/

.product-name {
    width: 100%;
    min-width: 0;

    margin: 0 0 10px 0;

    font-size: 15px;

    line-height: 21px;

    font-weight: 600;

    height: 42px;

    min-height: 42px;

    max-height: 42px;

    overflow: hidden;

    display: -webkit-box;

    -webkit-box-orient: vertical;

    -webkit-line-clamp: 2;

    line-clamp: 2;

    word-break: break-word;

    flex-shrink: 0;
}


/*
|--------------------------------------------------------------------------
| RATING
|--------------------------------------------------------------------------
*/

.rating {
    margin: 0 0 12px 0;

    font-size: 13px;

    color: #666;

    min-height: 18px;

    line-height: 18px;

    flex-shrink: 0;
}

.rating-empty {
    visibility: hidden;
}


/*
|--------------------------------------------------------------------------
| PRODUCT FOOTER
|--------------------------------------------------------------------------
*/

.product-footer {
    width: 100%;
    min-width: 0;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-top: auto;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| PRICE
|--------------------------------------------------------------------------
*/

.price {
    min-width: 0;

    font-size: 16px;

    font-weight: 700;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| VIEW BUTTON
|--------------------------------------------------------------------------
*/

.view-btn {
    display: inline-block;

    padding: 7px 10px;

    border-radius: 5px;

    background: #222;

    color: #ffffff;

    text-decoration: none;

    font-size: 12px;

    white-space: nowrap;

    flex-shrink: 0;

    transition:
        background 0.2s ease;
}

.view-btn:hover {
    background: #000;

    color: #ffffff;
}


/*
|--------------------------------------------------------------------------
| NO PRODUCTS
|--------------------------------------------------------------------------
*/

.no-products {
    width: 100%;

    padding: 30px;

    text-align: center;

    background: #ffffff;

    border: 1px solid #e5e5e5;

    border-radius: 10px;

    color: #666;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 1200px
|--------------------------------------------------------------------------
*/

@media (max-width: 1200px) {

    .products-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 992px
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .input-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .products-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 576px
|--------------------------------------------------------------------------
*/

@media (max-width: 576px) {

    .search-wrapper {
        padding: 15px;
    }

    .input-grid {
        grid-template-columns:
            minmax(0, 1fr);

        gap: 14px;
    }

    .products-grid {
        grid-template-columns:
            minmax(0, 1fr);
    }

    .category-header {
        flex-direction:
            column;

        align-items:
            flex-start;
    }

    .page-header h1 {
        font-size: 26px;
    }

    .product-image-wrapper {
        height: 220px;
    }

    .button-wrapper {
        text-align: left;
    }

}

</style>