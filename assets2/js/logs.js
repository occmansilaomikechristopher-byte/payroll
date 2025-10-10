$('#lightboxModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var imageSrc = button.data('image'); // Extract info from data-* attributes
    var modal = $(this);
    modal.find('.modal-body #lightboxImage').attr('src', imageSrc);
});