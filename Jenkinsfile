pipeline {
  agent none
  stages { 
    stage("deploy") {
      stages {

        stage("staging") {
          when { branch 'staging' }
          agent { label 'staging' }
          steps {
		    sh 'cp -r images /var/www/shop-test.nokware.net'
            sh 'cp -r includes /var/www/shop-test.nokware.net'
          }
        }
		
		stage ("production"){
		  when { branch 'master' }
		  agent { label 'production' }
		  stages {
		    stage("backtothelan.com") {
			  steps {
			    sh 'cp -r images /var/www/backtothelan.com'
			    sh 'cp -r includes /var/www/backtothelan.com'
			  }
			}
			
			stage("shop.nokware.net") {
			  steps {
			    sh 'cp -r images /var/www/shop.nokware.net'			    
			    sh 'cp -r includes /var/www/shop.nokware.net'
			  }
			}
			
			stage("sugarhousecoins.com") {
			  steps {
			    sh 'cp -r images /var/www/mishochu.com/shop'
			    sh 'cp -r includes /var/www/sugarhousecoins.com'
			  }
			}
			
			stage("mishochu.com/shop") {
			  steps {
			    sh 'cp -r images /var/www/mishochu.com/shop'			  
			    sh 'cp -r includes /var/www/mishochu.com/shop'
			  }
			}
		  }
		}
      }
    }
  }
}
