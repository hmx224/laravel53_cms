<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">商品详情</div>

                    <div class="panel-body">
                        <div class="bs-callout bs-callout-danger">
                            <img :src="product.image_url" alt="" width="320">
                            <p>{{ product.name }}</p>
                            <p>{{ product.price  }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            axios.get('/api/products/info?id=' + this.$route.params.id).then(response => {
                this.product = response.data.data;
            });
        },
        data() {
            return {
                product: {}
            };
        }
    }
</script>
