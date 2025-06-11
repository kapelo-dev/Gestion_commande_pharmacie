<div class="modal-body">
    <form action="{{ route('pharmaciens.update', $pharmacienId) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="{{ $data['nom'] }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="{{ $data['prenom'] }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="identifiant">Identifiant</label>
                    <input type="text" class="form-control" id="identifiant" name="identifiant" value="{{ $data['identifiant'] }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" value="{{ $data['telephone'] }}" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="d-block mb-2">Rôle</label>
            <div class="radio-container">
                <div class="custom-radio">
                    <input type="radio" id="role_gerant" name="role" value="gérant" {{ $data['role'] == 'gérant' ? 'checked' : '' }} required>
                    <label for="role_gerant">Gérant</label>
                </div>
                <div class="custom-radio">
                    <input type="radio" id="role_caissier" name="role" value="caissier" {{ $data['role'] == 'caissier' ? 'checked' : '' }} required>
                    <label for="role_caissier">Caissier</label>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
