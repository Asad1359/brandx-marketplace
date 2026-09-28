import api from './api';

/*
|--------------------------------------------------------------------------
| GET MARKETPLACE SEARCH HISTORY
|--------------------------------------------------------------------------
*/

export const getMarketplaceSearches = () => {
    return api.get('/marketplace');
};

/*
|--------------------------------------------------------------------------
| START MARKETPLACE SEARCH
|--------------------------------------------------------------------------
*/

export const searchMarketplace = (query) => {
    const cleanQuery = String(query || '').trim();

    if (!cleanQuery) {
        throw new Error('Please enter a product name.');
    }

    return api.post('/marketplace/search', {
        query: cleanQuery,
    });
};

/*
|--------------------------------------------------------------------------
| GET SEARCH STATUS
|--------------------------------------------------------------------------
*/

export const getMarketplaceStatus = (id) => {
    return api.get(`/marketplace/status/${id}`);
};