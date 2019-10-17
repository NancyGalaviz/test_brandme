<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Welcome to facebook app</div>
             <div class="form-group">
                <input type="text" v-model="token" class="form-control" id="token" placeholder="Token">
            </div>
            <button @click="verifyToken()" class="btn btn-primary ">verificar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      info: null,
      verified: null,
      token:null
    };
  },
  async mounted() {
    let url = window.location.href
    let query = window.location.search.substring(1)
    if(url.split("?").length>0){
      query = url.split("?")[1];
    }
    this.info = (/^[?#]/.test(query) ? query.slice(1) : url)
    .split("&")
    .reduce((params, param) => {
        let [key, value] = param.split("=")
        params[key] = value
        ? decodeURIComponent(value.replace(/\+/g, " "))
        : ""
        return params
    }, {})
    let balance = await fetch('/savetoken', {
        method: 'POST',
        body: JSON.stringify(this.info),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    this.token = this.info.access_token
    let uri = window.location.toString()
	if (uri.indexOf("?") > 0) {
	    let clean_uri = uri.substring(0, uri.indexOf("?"))
	    window.history.replaceState({}, document.title, clean_uri)
	}
  },
  method: {
      verifyToken: async function() {
        try {
            let verified = await fetch('/verify/'+this.token, {
                method: 'GET'
            })
            let verifiedData = await verified.json()
            if (verifiedData && verifiedData === 'object' ) {
                this.verified = verifyData.response
            }
        } catch (error) {

        }
    }
  }
};
</script>
