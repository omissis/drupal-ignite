module.exports = function (grunt) {
    "use strict";

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        uglify: {
            options: {
                report: 'min',
                preserveComments: false
            },
            build: {
                files: {
                    'assets/app.min.js' : ["js/jquery.utils.js", "js/jquery.sticky.js", "js/config.js"]
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
                    'assets/styles.min.css' : ["css/skel-noscript.css", "css/style.css", "css/style-desktop.css", "css/drupal-ignite.css"]
                }
            }
        },

        watch: {
            less: {
                files: 'css/*.css',
                tasks: ['clean:css', 'cssmin']
            },
            js: {
                files: 'js/*.js',
                tasks: ['clean:js', 'uglify']
            }
        },

        clean: {
            all: ['assets/*'],
            css: ['assets/*.css'],
            js: ['assets/*.js']
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-shell');

    grunt.registerTask('default', ['clean', 'uglify', 'cssmin']);
};
