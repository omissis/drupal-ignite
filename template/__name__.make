core = 7.x

api = 2

projects[] = "drupal"

; Modules

;; Contrib
projects[boxes][subdir] = "contrib"
projects[boxes][version] = "1.1"

projects[ctools][subdir] = "contrib"
projects[ctools][version] = "1.4"

projects[context][subdir] = "contrib"
projects[context][version] = "3.2"

projects[date][subdir] = "contrib"
projects[date][version] = "2.7"

projects[entity][subdir] = "contrib"
projects[entity][version] = "1.5"

projects[entitycache][subdir] = "contrib"
projects[entitycache][version] = "1.2"

projects[features][subdir] = "contrib"
projects[features][version] = "2.0"

projects[libraries][subdir] = "contrib"
projects[libraries][version] = "2.2"

projects[file_entity][subdir] = "contrib"
projects[file_entity][version] = "2.0-alpha3"

projects[migrate][subdir] = "contrib"
projects[migrate][version] = "2.5"

projects[migrate_extras][subdir] = "contrib"
projects[migrate_extras][version] = "2.5"

projects[references][subdir] = "contrib"
projects[references][version] = "2.1"

projects[rules][subdir] = "contrib"
projects[rules][version] = "2.7"

projects[strongarm][subdir] = "contrib"
projects[strongarm][version] = "2.0"

projects[token][subdir] = "contrib"
projects[token][version] = "1.5"

projects[views][subdir] = "contrib"
projects[views][version] = "3.7"

projects[wysiwyg][subdir] = "contrib"
projects[wysiwyg][version] = "2.2"

;; Devel
projects[coder][subdir] = "devel"
projects[coder][version] = "2.2"

projects[devel][subdir] = "devel"
projects[devel][version] = "1.5"

projects[diff][subdir] = "devel"
projects[diff][version] = "3.2"

projects[masquerade][subdir] = "devel"
projects[masquerade][version] = "1.0-rc7"

; Themes
;projects[omega][version] = "4.2"
projects[tao][version] = "3.1"
projects[rubik][version] = "4.0"

; Libraries
;; CKEditor
libraries[ckeditor][download][type] = "get"
libraries[ckeditor][download][url] = "http://download.cksource.com/CKEditor/CKEditor/CKEditor%204.4.0/ckeditor_4.4.0_standard.zip"
libraries[ckeditor][directory_name] = "ckeditor"
libraries[ckeditor][type] = "library"
