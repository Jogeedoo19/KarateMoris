$(document).ready(function() {
    $("#video-list").sortable({
        handle: '.handle',
        axis: 'y',
        update: function(event, ui) {
            var order = [];
            $('#video-list tr').each(function(index) {
                order.push({
                    id: $(this).data('id'),
                    position: index + 1
                });
            });
            
            // Send the new order to the server
            $.ajax({
                url: 'update_video_position.php',
                type: 'POST',
                data: { order: order },
                success: function(response) {
                    console.log('Order updated successfully');
                },
                error: function(xhr, status, error) {
                    console.error('Error updating order:', error);
                }
            });
        }
    });
    $("#video-list").disableSelection();
});