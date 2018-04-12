define(['jquery', 'jqueryui'], function($, ui) {
    return {
        init: function() {
            // hide/show room button
            $('a.hide-button').on('click', function(e) {
                e.preventDefault();

                var roomid = $(this).data('room');
                var $room = $(this).closest('.room');

                $.ajax({
                    method: 'POST',
                    url: M.cfg.wwwroot + '/mod/ciscospark/ajax/room_visibility.php',
                    data: {
                        room: roomid
                    }
                }).done(function(result) {
                    if (result === 1) {
                        if ($($room).hasClass('hidden-room')) {
                            $($room).removeClass('hidden-room');
                        }
                        else {
                            $($room).addClass('hidden-room');
                        }
                    }
                });
            });
        }
    };
});

