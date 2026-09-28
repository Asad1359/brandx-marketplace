import api from './api';

/*
|--------------------------------------------------------------------------
| GET MARKETPLACE SEARCH HISTORY
|--------------------------------------------------------------------------
*/

export async function getMarketplaceSearches() {
    return api.get('/marketplace');
}

/*
|--------------------------------------------------------------------------
| START MARKETPLACE SEARCH
|--------------------------------------------------------------------------
*/

export async function searchMarketplace(query) {
    const cleanQuery = String(query || '').trim();

    if (!cleanQuery) {
        throw new Error('Please enter a product name.');
    }

    return api.post('/marketplace/search', {
        query: cleanQuery,
    });
}

/*
|--------------------------------------------------------------------------
| GET SEARCH STATUS
|--------------------------------------------------------------------------
*/

export async function getMarketplaceStatus(id) {
    return api.get(`/marketplace/status/${id}`);
}