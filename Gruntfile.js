/* global module:false */
module.exports = function(grunt) {

   // Project configuration.
   grunt.initConfig({
      app: grunt.file.readJSON('package.json'),
      meta: {
         banner: grunt.file.read('js/jsxc/banner.js')
      },
      jshint: {
         options: {
            jshintrc: '.jshintrc'
         },
         gruntfile: {
            src: 'Gruntfile.js'
         },
         files: [ 'js/ojsxc.js' ]
      },
      copy: {
         main: {
            files: [ {
               expand: true,
               src: [ 'js/*.js', 'css/*', 'appinfo/*', 'ajax/*', 'img/**', 'templates/*', 'sound/*', 'settings.php', 'LICENSE' ],
               dest: 'build/'
            }, {
               expand: true,
               cwd: 'js/jsxc/build/',
               src: [ '**' ],
               dest: 'build/js/jsxc/'
            } ]
         }
      },
      clean: [ 'build/' ],
      usebanner: {
         dist: {
            options: {
               position: 'top',
               banner: '<%= meta.banner %>'
            },
            files: {
               src: [ 'build/js/*.js', 'build/css/jsxc.oc.css' ]
            }
         }
      },
      replace: {
         info: {
            src: [ 'build/appinfo/info.xml' ],
            overwrite: true,
            replacements: [ {
               from: /<version>[\d.]+<\/version>/,
               to: "<version><%= app.version %></version>"
            } ]
         },
         version: {
            src: [ 'build/appinfo/version' ],
            overwrite: true,
            replacements: [ {
               from: /[\d.]+/,
               to: "<%= app.version %>"
            } ]
         }
      },
      search: {
         console: {
            files: {
               src: [ 'js/*.js' ]
            },
            options: {
               searchString: /console\.log\((?!'[<>]|msg)/g,
               logFormat: 'console',
               failOnMatch: true
            }
         },
         changelog: {
            files: {
               src: [ 'CHANGELOG.md' ]
            },
            options: {
               searchString: "<%= app.version %>",
               logFormat: 'console',
               onComplete: function(m) {
                  if (m.numMatches === 0) {
                     grunt.fail.fatal("No entry in README.md for current version found.");
                  }
               }
            }
         }
      },
      compress: {
         main: {
            options: {
               archive: "archives/ojsxc-<%= app.version %>.zip"
            },
            files: [ {
               src: [ '**' ],
               expand: true,
               dest: 'ojsxc/',
               cwd: 'build/'
            } ]
         }
      },
      autoprefixer: {
         no_dest: {
             src: 'css/*.css'
         }
      },
      sass: {
         options: {
            imagePath: '../js/jsxc/img'
         },
         dist: {
             files: {
                'css/jsxc.oc.css': 'scss/jsxc.oc.scss'
             }
         }
       },
       dataUri: {
          dist: {
            src: 'css/jsxc.oc.css',
            dest: 'build/css/',
            options: {
              target: ['img/*.*', 'js/jsxc/img/*.*', 'js/jsxc/img/**/*.*'],
              /*fixDirLevel: true,
              baseDir: './',*/
              maxBytes: 2048
            }
          }
        }
   });

   // These plugins provide necessary tasks.
   grunt.loadNpmTasks('grunt-contrib-jshint');
   grunt.loadNpmTasks('grunt-contrib-copy');
   grunt.loadNpmTasks('grunt-contrib-clean');
   grunt.loadNpmTasks('grunt-banner');
   grunt.loadNpmTasks('grunt-text-replace');
   grunt.loadNpmTasks('grunt-search');
   grunt.loadNpmTasks('grunt-contrib-compress');
   grunt.loadNpmTasks('grunt-sass');
   grunt.loadNpmTasks('grunt-autoprefixer');
   grunt.loadNpmTasks('grunt-data-uri');

   // Default task.
   grunt.registerTask('default', [ 'jshint', 'search', 'clean', 'css', 'copy', 'dataUri', 'usebanner', 'replace', 'compress' ]);

   grunt.registerTask('pre', [ 'jshint', 'search:console', 'clean', 'css', 'copy', 'dataUri', 'usebanner', 'replace', 'compress' ]);

   grunt.registerTask('css', [ 'sass', 'autoprefixer' ]);
};
