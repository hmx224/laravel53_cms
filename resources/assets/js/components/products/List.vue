<template>
    <div>
        <nav-header></nav-header>
        <nav-breadcrumb>
            <span slot="bread">商品列表</span>
        </nav-breadcrumb>
        <div class="accessory-result-page accessory-page">
            <div class="container">
                <div class="filter-nav">
                    <span class="sortby">Sort by:</span>
                    <a href="javascript:void(0)" class="default cur">Default</a>
                    <a href="javascript:void(0)" class="price">Price
                        <svg class="icon icon-arrow-short">
                            <use xlink:href="#icon-arrow-short"></use>
                        </svg>
                    </a>
                    <a href="javascript:void(0)" class="filterby stopPop">Filter by</a>
                </div>
                <div class="accessory-result">
                    <!-- filter -->
                    <div class="filter stopPop" id="filter">
                        <dl class="filter-price">
                            <dt>Price:</dt>
                            <dd><a href="javascript:void(0)">All</a></dd>
                            <dd>
                                <a href="javascript:void(0)">0 - 100</a>
                            </dd>
                            <dd>
                                <a href="javascript:void(0)">100 - 500</a>
                            </dd>
                            <dd>
                                <a href="javascript:void(0)">500 - 1000</a>
                            </dd>
                            <dd>
                                <a href="javascript:void(0)">1000 - 2000</a>
                            </dd>
                        </dl>
                    </div>

                    <!-- search result accessories list -->
                    <div class="accessory-list-wrap">
                        <div class="accessory-list col-4">
                            <ul>
                                <li v-for="product in products">
                                    <div class="pic">
                                        <router-link :to="{ name: 'ProductDetail', params: {id: product.id} }">
                                            <img v-lazy="product.image_url" alt="">
                                        </router-link>
                                    </div>
                                    <div class="main">
                                        <div class="name">{{product.name}}</div>
                                        <div class="price">{{product.price}}</div>
                                        <div class="btn-area">
                                            <a href="javascript:;" class="btn btn--m">加入购物车</a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav-footer></nav-footer>
    </div>
</template>

<script>
    import NavHeader from '../Header'
    import NavFooter from '../Footer.vue'
    import NavBreadcrumb from '../Breadcrumb'

    export default {
        components: {
            NavHeader,
            NavFooter,
            NavBreadcrumb,
        },
        data() {
            return {
                products: []
            };
        },
        mounted() {
            axios.get('/api/products?site_id=2&category_id=37').then(response => {
                this.products = response.data.data;
            });
        }
    }
</script>