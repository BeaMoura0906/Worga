import "jquery"
import "bootstrap"
import "bootstrap-table"
import "bootstrap-table-fr-FR"

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

function sendFormData( url, formData ) {
return fetch(url, {
                method: 'POST',
                body: formData
            })
    .then(response => {
        if (!response.ok) {
            throw new Error( response.url + ' ' + response.status + ' ' + response.statusText );
        }
        return response.json();
    });
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

$('#editFinTransModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget)
    const url = button.data('url')

    const paramList = {
        url: url
    }

    sendFetch( paramList )
        .then( data => {
            $('#editFinTransModal #finTransId').val(data.finTransId)
            $('#editFinTransModal #categoryId').val(data.category)
            $('#editFinTransModal #dateId').val(data.date)
            $('#editFinTransModal #titleId').val(data.title)
            $('#editFinTransModal #descriptionId').val(data.description)
            $('#editFinTransModal #amountExVatId').val(data.amountExVat)
            $('#editFinTransModal #vatRateId').val(data.vatRate)
            $('#editFinTransModal #insertedAtId').text(data.insertedAt)
            $('#editFinTransModal #updatedAtId').text(data.updatedAt)
            $('#editFinTransModal #userLoginId').text(data.user)
        })
        .catch(error => {
            console.error( 'Erreur :', error )
        })
});

$('#viewFinTransModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget)
    const url = button.data('url')

    const paramList = {
        url: url
    }

    sendFetch( paramList )
        .then( data => {
            $('#viewFinTransModal #alertDiv').hide()
            $('#viewFinTransModal #alertDiv').attr('class', '')
            $('#viewFinTransModal #alertMessage').text('')
            $('#viewFinTransModal #accountId').val(data.accountId)
            $('#viewFinTransModal #finTransId').val(data.finTransId)
            $('#viewFinTransModal #categoryId').text(data.category)
            const formattedDate = new Date(data.date).toLocaleDateString('fr-FR');
            $('#viewFinTransModal #dateId').text(formattedDate);
            $('#viewFinTransModal #titleId').text(data.title)
            $('#viewFinTransModal #descriptionId').text(data.description)
            const formattedAmountIncVat = data.amountIncVat + ' €'
            $('#viewFinTransModal #amountIncVatId').text(formattedAmountIncVat)
            const formattedAmountExVat = data.amountExVat + ' €'
            $('#viewFinTransModal #amountExVatId').text(formattedAmountExVat)
            const formattedVatRate = data.vatRate + ' %'
            $('#viewFinTransModal #vatRateId').text(formattedVatRate)
            $('#viewFinTransModal #insertedAtId').text(data.insertedAt)
            $('#viewFinTransModal #updatedAtId').text(data.updatedAt)
            $('#viewFinTransModal #userLoginId').text(data.user)
            if(data.docPath && data.docPath !== null) {
                $('#viewFinTransModal #docForm').hide()
                $('#viewFinTransModal #pdfViewer').attr('src', data.docPath + '#toolbar=0&navpanes=0')
                $('#viewFinTransModal #docNameToDisplayId').text(data.docName)
                $('#viewFinTransModal #docDiv').show()
            } else {
                $('#viewFinTransModal #pdfViewer').attr('src', '')
                $('#viewFinTransModal #docDiv').hide()
                $('#viewFinTransModal #docForm').show()
            }
        })
        .catch(error => {
            console.error( 'Erreur :', error )
        })
});

function updateModalWithResponse(data) {
    if(data.success === true) {
        $('#viewFinTransModal #alertDiv').show()
        $('#viewFinTransModal #alertDiv').attr('class', 'alert alert-success alert-dismissible fade show')
        $('#viewFinTransModal #alertMessage').text(data.message)
        $('#viewFinTransModal #pdfViewer').attr('src', data.docPath + '#toolbar=0&navpanes=0')
        $('#viewFinTransModal #docNameToDisplayId').text(data.docName)
        $('#viewFinTransModal #docDiv').show()
        $('#viewFinTransModal #docForm')[0].reset()
        $('#viewFinTransModal #docForm').hide()
    } else {
        $('#viewFinTransModal #alertDiv').show()
        $('#viewFinTransModal #alertDiv').attr('class', 'alert alert-warning alert-dismissible fade show')
        $('#viewFinTransModal #alertMessage').text(data.message)
        $('#viewFinTransModal #docDiv').hide()
        $('#viewFinTransModal #docForm').show()
    }
    $('#viewFinTransModal').modal('show')
}

$('#docForm').on('submit', function(e) {
    e.preventDefault()

    const formData = new FormData(this)
    const actionUrl = this.action

    sendFormData(actionUrl, formData) 
        .then(data => {
            console.log('Success:', data)
            updateModalWithResponse(data)
        })
        .catch(error => {
            console.error('Error:', error)
        })
});

$('#deleteDocLinkId').on('click', function(e) {
    if(!confirm('Voulez-vous supprimer ce document ? Cette action est irréversible.')) {
        e.preventDefault()
        return false
    }

    const btn = $(this)
    const finTransId = $('#viewFinTransModal #finTransId').val()
    const url = btn.data('delete-url') + finTransId

    const paramList = {
        url: url
    }

    sendFetch( paramList )
        .then( data => {
            if(data.success === true) {
                console.log('Success:', data)
                $('#viewFinTransModal #docDiv').hide()
                $('#viewFinTransModal #docForm').show()
                $('#viewFinTransModal #alertDiv').show()
                $('#viewFinTransModal #alertDiv').attr('class', 'alert alert-success alert-dismissible fade show')
                $('#viewFinTransModal #alertMessage').text(data.message)
            } else {
                console.log('Error:', data)
                $('#viewFinTransModal #alertDiv').show()
                $('#viewFinTransModal #alertDiv').attr('class', 'alert alert-warning alert-dismissible fade show')
                $('#viewFinTransModal #alertMessage').text(data.message)
            }
        })
        .catch(error => {
            console.error( 'Erreur :', error )
        })
});

$('#deleteFinTransBtn').on('click', function(e) {
    if(!confirm('Voulez-vous supprimer cette transaction ? Cette action est irréversible.')) {
        e.preventDefault()
        return false
    }

    const btn = $(this)
    const finTransId = $('#editFinTransModal #finTransId').val()
    const url = btn.data('url') + finTransId

    window.location.href = url
})