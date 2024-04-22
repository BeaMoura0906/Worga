import 'jquery'
import "bootstrap"
import "bootstrap-table"


function sendFetch( paramList, options={}, responseType = 'json' ) {
    if (typeof options === "object") {
        options = {
            method: 'POST',
            body: JSON.stringify(paramList)
        }
    }

    return fetch(paramList.url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error( response.url + ' ' + response.status + ' ' + response.statusText )
            }
            if (responseType === 'text') {
                return response.text()
            } else {
                return response.json()
            }
        })
}

$('#users-table').on( 'change', '.active-line', function(e){
    const elem = e.target;
    const isActive = elem.dataset.isactive == '0' ? '1' : '0';
    const url = elem.dataset.url + isActive;

    console.log('Nouvel état actif :', isActive);
    elem.dataset.isactive = isActive;

    const paramList = {
        url: url
    }

    sendFetch( paramList )
        .then( response => {
            return response
        })
        .then( data => {
            console.log( data )
        })
        .catch(error => {
            console.error( 'Erreur :', error )
        })

})

$('#clients-table').on( 'change', '.active-line', function(e){
    const elem = e.target;
    const url = elem.dataset.url;

    const paramList = {
        url: url
    }

    sendFetch( paramList )
        .then( response => {
            return response
        })
        .then( data => {
            console.log( data )
        })
        .catch(error => {
            console.error( 'Erreur :', error )
        })

})