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
         build: {
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
         },
         css: {
            files: [ {
               expand: true,
               cwd: 'js/jsxc/lib/',
               src: ['*.css'],
               dest: 'css/'
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
        },
        watch: {
            css: {
                files: ['js/jsxc/scss/*', 'scss/*'],
                tasks: ['sass', 'autoprefixer']
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
   grunt.loadNpmTasks('grunt-contrib-watch');

   grunt.registerTask('default', [ 'copy:css', 'sass', 'autoprefixer', 'watch' ]);

   grunt.registerTask('build:prerelease', [ 'jshint', 'search:console', 'clean', 'sass', 'autoprefixer', 'copy:css', 'copy:build', 'dataUri', 'usebanner', 'replace', 'compress' ]);

   grunt.registerTask('build:release', [ 'search:changelog', 'build:prerelease' ]);

   // @deprecated
   grunt.registerTask('pre', [ 'build:prerelease' ]);
};
