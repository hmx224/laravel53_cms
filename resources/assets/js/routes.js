import VueRouter from 'vue-router';

let routes = [
    {
        path: '/mall/',
        component: require('./components/products/List')
    },
    {
        path: '/mall/products/:id',
        name: 'ProductDetail',
        component: require('./components/products/Detail')
    },
    {
        path: '/mall/cart',
        component: require('./components/carts/Detail')
    },
];

export default new VueRouter({
    mode: 'history',
    routes
})