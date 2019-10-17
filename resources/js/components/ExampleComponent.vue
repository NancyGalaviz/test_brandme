<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>
                                                {{ info }}
                    
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data (){
            return {
                info: 'Hola'
            }
        },
        mounted () {
            window.location = "https://www.facebook.com/v4.0/dialog/oauth?client_id=371656837051056&display=popup&response_type=token&redirect_uri=https://2df43433.ngrok.io/token&scopes=publish_pages,manage_pages"
        }
    }
</script>
