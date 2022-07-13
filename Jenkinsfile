pipeline {
  agent none
  stages {
    stage("deploy") {
      stages {
        stage("staging") {
          when { branch 'staging' }
          agent { label 'staging' }
          steps {
            sh 'sudo cp -R public_html/admin/* /var/www/shop-test.nokware.net/admin/'
            sh 'sudo cp -R public_html/includes/* /var/www/shop-test.nokware.net/includes/'
            sh 'sudo cp -R public_html/vqmod/* /var/www/shop-test.nokware.net/vqmod/'
            sh 'sudo chown -R www-data:www-data /var/www/shop-test.nokware.net/'
            :qa

          }
        }
      }
    }
  }
}
