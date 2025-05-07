@if(session('alertes_expiration'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let alertesHtml = '<div class="table-responsive"><table class="table table-sm">';
            alertesHtml += '<thead><tr><th>Produit</th><th>Date d\'expiration</th><th>Quantité</th></tr></thead><tbody>';
            
            @foreach(session('alertes_expiration') as $alerte)
                alertesHtml += '<tr>';
                alertesHtml += '<td>{{ $alerte['produit'] }}</td>';
                alertesHtml += '<td>{{ $alerte['date_expiration'] }}</td>';
                alertesHtml += '<td>{{ $alerte['quantite'] }}</td>';
                alertesHtml += '</tr>';
            @endforeach
            
            alertesHtml += '</tbody></table></div>';

            Swal.fire({
                title: 'Attention : Produits proche de la date d\'expiration',
                html: alertesHtml,
                icon: 'warning',
                confirmButtonText: 'Compris',
                customClass: {
                    container: 'custom-swal-container',
                    popup: 'custom-swal-popup',
                    content: 'custom-swal-content'
                }
            });
        });
    </script>

    <style>
        .custom-swal-container {
            z-index: 9999;
        }
        .custom-swal-popup {
            max-width: 600px;
            width: 90%;
        }
        .custom-swal-content {
            max-height: 70vh;
            overflow-y: auto;
        }
        .table-responsive {
            margin: 1rem 0;
        }
        .table {
            margin-bottom: 0;
        }
        .table th, .table td {
            padding: 0.5rem;
            text-align: left;
        }
    </style>
@endif 