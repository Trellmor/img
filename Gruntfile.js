module.exports = function (grunt) {
  'use strict';

  // Force use of Unix newlines
  grunt.util.linefeed = '\n';

  var configBridge = grunt.file.readJSON('./node_modules/bootstrap/grunt/configBridge.json', { encoding: 'utf8' });

  // Project configuration.
  grunt.initConfig({

    // Metadata.
    pkg: grunt.file.readJSON('package.json'),

    // Task configuration.
    clean: {
      build: 'build'
    },

    concat: {
      options: {
        sourceMap: true
      },
      site: {
        src: [
          'js/gallery.js',
          'js/upload.js',
          'js/signin.js',
          'js/tags.js'
        ],
        dest: 'build/js/site.js',
      }
    },

    uglify: {
      options: {
        mangle: true,
        sourceMap: true,
        sourceMapIncludeSources: true
      },
      site: {
        options: {
          sourceMapIn: 'build/js/site.js.map'
        },
        src: 'build/js/site.js',
        dest: 'build/js/site.min.js'
      }
    },

    less: {
      compileBootstrap: {
        options: {
          strictMath: true,
          sourceMap: true,
          outputSourceFiles: true,
          sourceMapURL: 'bootstrap.css.map',
          sourceMapFilename: 'build/css/bootstrap.css.map'
        },
        src: 'less/bootstrap.less',
        dest: 'build/css/bootstrap.css'
      },
      compileSite: {
        options: {
          strictMath: true,
          sourceMap: true,
          outputSourceFiles: true,
          sourceMapURL: 'site.css.map',
          sourceMapFilename: 'build/css/site.css.map'
        },
        src: 'less/site.less',
        dest: 'build/css/site.css'
      }
    },

    autoprefixer: {
      options: {
        browsers: configBridge.config.autoprefixerBrowsers
      },
      bootstrap: {
        options: {
          map: true
        },
        src: 'build/css/bootstrap.css'
      },
      site: {
        options: {
          map: true
        },
        src: 'build/css/site.css'
      }
    },

    cssmin: {
      options: {
        // TODO: disable `zeroUnits` optimization once clean-css 3.2 is released
        //    and then simplify the fix for https://github.com/twbs/bootstrap/issues/14837 accordingly
        compatibility: 'ie8',
        keepSpecialComments: '*',
        sourceMap: true,
        advanced: false
      },
      minifyBootstrap: {
        src: 'build/css/bootstrap.css',
        dest: 'build/css/bootstrap.min.css'
      },
      minifySite: {
        src: 'build/css/site.css',
        dest: 'build/css/site.min.css'
      }
    },

    csscomb: {
      options: {
        config: 'node_modules/bootstrap/less/.csscomb.json'
      },
      build: {
        expand: true,
        cwd: 'build/css/',
        src: ['*.css', '!*.min.css'],
        dest: 'build/css/'
      },
    },

    copy: {
      css: {
        expand: true,
        flatten: true,
        src: [
          'node_modules/select2/dist/css/select2.min.css',
          'node_modules/select2-bootstrap-theme/dist/select2-bootstrap.min.css',
          'node_modules/blueimp-gallery/css/blueimp-gallery.min.css',
          'build/css/bootstrap.min.css',
          'build/css/bootstrap.min.css.map',
          'build/css/site.min.css',
          'build/css/site.min.css.map'
        ],
        dest: 'public/css/'
      },
      js: {
        expand: true,
        flatten: true,
        src: [
          'node_modules/bootstrap/dist/js/bootstrap.min.js',
          'node_modules/select2/dist/js/select2.min.js',
          'node_modules/blueimp-gallery/js/jquery.blueimp-gallery.min.js',
          'vendor/moxiecode/plupload/js/plupload.full.min.js',
          'build/js/site.min.js',
          'build/js/site.min.js.map'
        ],
        dest: 'public/js/'
      },
      fonts: {
        expand: true,
        flatten: true,
        src: 'node_modules/bootstrap/dist/fonts/*',
        dest: 'public/fonts/'
      },
      img: {
        expand: true,
        flatten: true,
        src: 'node_modules/blueimp-gallery/img/*',
        dest: 'public/img/'
      }
    },

    exec: {
      composer: {
        command: 'composer install'
      }
    }
  });


  // These plugins provide necessary tasks.
  require('load-grunt-tasks')(grunt, { scope: 'devDependencies' });

  // CSS distribution task.
  grunt.registerTask('less-compile', ['less:compileBootstrap', 'less:compileSite']);
  grunt.registerTask('build-css', ['less-compile', 'autoprefixer:bootstrap', 'autoprefixer:site', 'csscomb:build', 'cssmin:minifyBootstrap', 'cssmin:minifySite', 'copy:css']);

  // JS distribution task.
  grunt.registerTask('build-js', ['concat:site', 'uglify:site', 'copy:js']);

  // Full distribution task.
  grunt.registerTask('build', ['clean:build', 'exec:composer', 'build-css', 'copy:fonts', 'copy:img', 'build-js']);

};

/* vim: set tabstop=2 expandtab softtabstop=2 shiftwidth=2: */
