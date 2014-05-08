module.exports = function (grunt) {
    "use strict";

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        'string-replace': {
          dist: {
            files: {
              './': ['index.html', 'introduction.html'], // includes files in dir
            },
            options: {
              replacements: [{
                pattern: /var\ version\ \=\ \d+\;/i,
                replacement: "var version = " + Math.random().toString().slice(2) + ";"
              }]
            }
          }
        },

        uglify: {
            options: {
                report: 'min',
                preserveComments: false
            },
            build: {
                files: {
                    'assets/app.min.js' : ["js/ga.js", "js/jquery.utils.js", "js/jquery.sticky.js", "js/config.js"]
                }
            }
        },

        cssUrlRewrite: {
            build: {
                src: "assets/output.min.css",
                dest: "assets/styles.min.css",
                options: {
                    skipExternal: true,
                    rewriteUrl: function (url, options, dataURI) {
                        var imagesPathIndex = url.indexOf("/css/images");
                        if (imagesPathIndex != -1) {
                            return url.slice(imagesPathIndex);
                        }

                        var fontPathIndex = url.indexOf("/css/font");
                        if (fontPathIndex != -1) {
                            return url.slice(fontPathIndex);
                        }

                        return url;
                    }
                }
            }
        },

        cssmin: {
            options: {
                report: 'min',
                root: 'assets',
                target: 'assets',
                keepSpecialComments: 0
            },
            build: {
                files: {
                    'assets/output.min.css' : ["css/skel-noscript.css", "css/style.css", "css/style-desktop.css", "css/drupal-ignite.css"]
                }
            }
        },

        watch: {
            less: {
                files: 'css/*.css',
                tasks: ['clean:css', 'cssmin', 'cssUrlRewrite']
            },
            js: {
                files: 'js/*.js',
                tasks: ['clean:js', 'uglify']
            }
        },

        clean: {
            all: ['assets/*'],
            css: ['assets/*.css'],
            js: ['assets/*.js'],
            cssUrlRewrite: ['assets/output.min.css'],
            minification: ['assets/styles.min.css', 'assets/app.min.js']
        }
    });

    grunt.loadNpmTasks('grunt-css-url-rewrite');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.loadNpmTasks('grunt-string-replace');

    grunt.loadNpmTasks('grunt-shell');

    grunt.registerTask('default', ['clean', 'uglify', 'cssmin', 'cssUrlRewrite', 'clean:cssUrlRewrite', 'string-replace']);
};
