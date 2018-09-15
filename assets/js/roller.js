
      var i = 0;
      var done = false;
      function roll()
      {
        if (done) {
          $('#box5-value').removeClass('flipped');
          done = false;
          $('.canvas button').html('ROLL')
        }
        $('.canvas button').attr('disabled', true);
        $('#box1')
            .animate({
              left: -200+'px',
              marginTop: 2+'em'
            }, 500, 'easeOutBack', function() { i++; $(this).css('z-index', i) })
            .animate({
              left: 50+'%',
              marginTop: 0+'em'
            }, 500, 'easeOutBack');
        $('#box2')
            .animate({
              left: -200+'px',
              marginTop: 2+'em'
            }, 700, 'easeOutBack', function() { i++; $(this).css('z-index', i) })
            .animate({
              left: 50+'%',
              marginTop: 0+'em'
            }, 700, 'easeOutBack');
        $('#box3')
            .animate({
              left: -200+'px',
              marginTop: 2+'em'
            }, 900, 'easeOutBack', function() { i++; $(this).css('z-index', i) })
            .animate({
              left: 50+'%',
              marginTop: 0+'em'
            }, 900, 'easeOutBack');
        $('#box4')
            .animate({
              left: -200+'px',
              marginTop: 2+'em'
            }, 1100, 'easeOutBack', function() { i++; $(this).css('z-index', i) })
            .animate({
              left: 50+'%',
              marginTop: 0+'em'
            }, 1100, 'easeOutBack', function() {
              i = 0;
              $('#box5-value').addClass('flipped');
              var dice = (Math.floor(Math.random() * ((10-1)+1) + 1))-5;
              document.getElementById("value").innerHTML = dice;
              $('.canvas button').html('RICOMINCIA');
              $('.canvas button').attr('disabled', false);
              done = true;
            });
      }
