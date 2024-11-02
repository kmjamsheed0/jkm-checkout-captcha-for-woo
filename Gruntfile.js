'use strict';

module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        
        plugin: 'jkmccfw',
        dirs: {
            admin: {
                js: 'admin/assets/js',
                css: 'admin/assets/css'
            },
            public: {
                js: 'public/assets/js'
                // css: 'public/assets/css' // Commented out as public CSS is not present
            }
        },

        concat: {
            admin: {
                src: ['<%= dirs.admin.js %>/inc/**/*.js'],
                dest: '<%= dirs.admin.js %>/<%= plugin %>-admin.js',
            },
            public: {
                src: ['<%= dirs.public.js %>/inc/**/*.js'],
                dest: '<%= dirs.public.js %>/<%= plugin %>-public.js',
            },
        },
        uglify: {
            options: {
                mangle: false
            },
            my_target: {
                files: {
                    '<%= dirs.admin.js %>/<%= plugin %>-admin.min.js': ['<%= dirs.admin.js %>/<%= plugin %>-admin.js'],
                    '<%= dirs.public.js %>/<%= plugin %>-public.min.js': ['<%= dirs.public.js %>/<%= plugin %>-public.js']
                }
            }
        },
        cssmin: {
            options: {
                keepSpecialComments: 0
            },
            target: {
                files: {
                    '<%= dirs.admin.css %>/<%= plugin %>-admin.min.css': ['<%= dirs.admin.css %>/<%= plugin %>-admin.css']
                    // '<%= dirs.public.css %>/<%= plugin %>-public.min.css': ['<%= dirs.public.css %>/<%= plugin %>-public.css'] // Commented out
                }
            }
        },
        
        watch: {
            css: {
                files: ['<%= dirs.admin.css %>/<%= plugin %>-admin.css'], // Watching only admin CSS
                tasks: ['cssmin']
            },
            js: {
                files: ['<%= dirs.admin.js %>/inc/**/*.js', '<%= dirs.public.js %>/inc/**/*.js'], // Watching admin and public JS
                tasks: ['concat', 'uglify']
            }
        }
    });

    // Load the plugin that provides the necessary tasks.
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Default task(s).
    grunt.registerTask('default', ['watch']);
};
