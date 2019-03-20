/*!
 * @description GRUNT! (.js)
 */

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
          jQuery: true
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
    less: {
      development: {
        options: {
          paths: ['client/less'],
          yuicompress: false
        },
        files: {
          'client/css/wp-kneaver.css': 'client/less/wp-kneaver.less'
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
        tasks: ['jshint','concat','less'],
      },
      less: {
        files: 'client/less/*.less',
        tasks: ['less'],
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
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-copy');
  
  grunt.registerTask('deploy', ['jshint','concat','uglify','less','cssmin']);
  grunt.registerTask('default', ['jshint','concat','less','copy','watch']);
};

/* EOF */
