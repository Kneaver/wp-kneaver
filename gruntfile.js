/*!
 * @description GRUNT! (.js)
 */
/*global __dirname:true*/
/*global require:true*/

"use strict";

var path = require( "path" );

module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> v<%= pkg.version %>, <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      build: {
        src: 'client/js/app.js',
        dest: 'client/js/app.min.js'
      }
    },
    jshint: {
      options: {
        browser: true,
        globals: {
          // jQuery: true
        }
      },
      all: {
        files: {
          src: ['client/js/src/**/*.js']
        }
      }
    },
    concat: {
      options: {
      },
      dist: {
        src: [
          'client/js/src/app.js'
        ],
        dest: 'client/js/app.js'
      }
    },
    sass: {
      dist: {
        files: {
          'client/css/wp-kneaver.css': 'client/scss/wp-kneaver.scss',
        }
      }
    },
    cssmin: {
      compress: {
        files: {
          'client/css/wp-kneaver.min.css': ['client/css/wp-kneaver.css']
        }
      }
    },
    watch: {
      scripts: {
        files: ['Gruntfile.js','client/js/src/**/*.js','client/js/vendor/**/*.js'],
        tasks: ['jshint','concat','sass'],
      },
      less: {
        files: 'client/scss/*.scss',
        tasks: ['sass'],
      }
      ,
      css: {
        files: 'client/css/*.css',
        tasks: ['copy:css'],
      }
      ,
      php: {
        files: '*.php',
        tasks: ['copy:php'],
      }
    }
    ,
    copy: {
      php: {
        files: [
          // includes files within path
          // { src: ['wp-kneaver.php'], dest: 'C:\\inetpub\\kneaver.com\\wp-content\\plugins\\wp-kneaver\\wp-kneaver.php', filter: 'isFile'},
        ]
      }
      ,
      css: {
        files: [
        ]
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify-es');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-copy');
  
  grunt.registerTask('deploy', ['jshint','concat','uglify','sass','cssmin']);
  grunt.registerTask('default', ['jshint','concat','sass','copy','watch']);
};

/* EOF */
