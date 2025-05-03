<div class="modal-body">
    <form action="{{ route('pharmaciens.update', $pharmacienId) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="{{ $data['nom'] }}" required>
        </div>
        <div class="form-group">
            <label for="identifiant">Identifiant</label>
            <input type="text" class="form-control" id="identifiant" name="identifiant" value="{{ $data['identifiant'] }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $data['email'] }}" required>
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="{{ $data['telephone'] }}" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
