module.exports = function ( grunt ) {
  // Start out by loading the grunt modules we'll need
  require ( 'load-grunt-tasks' ) ( grunt );

  grunt.initConfig ({
    uglify : {
      production : {
        options : {
          beautify : false,
          preserveComments : false,
          mangle : {
            except : ['jQuery']
          }
        },
        files : {
          'assets/js/classic-yourls.min.js' : [
            'assets/js/classic-yourls.js'
          ],
          'assets/js/admin-footer.min.js' : [
            'assets/js/admin-footer.js'
          ]
        }
      }
    },

    autoprefixer : {
      options : {
        browsers : ['last 5 versions'],
        map : true
      },
      files : {
        expand : true,
        flatten : true,
        src : ['assets/css/classic-yourls.css'],
        dest : 'assets/css'
      }
    },

    cssmin : {
      target : {
        files : [{
          expand : true,
          cwd : 'assets/css',
          src : ['classic-yourls.css'],
          dest : 'assets/css',
          ext : '.min.css'
        }]
      }
    },

    sass : {
      dist : {
        options : {
          style : 'expanded',
          sourceMap : true,
          noCache : true
        },
        files : {
          'assets/css/classic-yourls.css' : 'assets/css/classic-yourls.scss'
        }
      }
    },

    makepot : {
      target : {
        options : {
          type : 'wp-plugin',
          domainPath : '/lang',
          mainFile : 'classic-yourls.php'
        }
      }
    },

    watch : {
      options : {
        livereload : true
      },
      scripts : {
        files : [
          'assets/js/**/*'
        ],
        tasks : ['uglify:production']
      },
      styles : {
        files : [
          'assets/css/*.scss'
        ],
        tasks : ['sass', 'autoprefixer', 'cssmin']
      }
    }
  });

  // A very basic default task.
  grunt.registerTask ( 'default', ['uglify:production', 'sass', 'autoprefixer', 'cssmin', 'makepot'] );
};
