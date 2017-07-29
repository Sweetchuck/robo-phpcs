#!groovy

node {
  env.ROBO_PHPCS_ENVIRONMENT = 'jenkins'

  manager.buildSuccess()

  try {
    wrap([$class: 'AnsiColorBuildWrapper']) {
      stage('Build') {
        checkout scm
        sh 'composer install --no-progress'
      }

      stage('QA') {
        sh 'bin/robo lint'
        sh 'bin/robo test'
      }

      stage('Reports') {
        step([
          $class: 'CheckStylePublisher',
          pattern: 'tests/_output/checkstyle/*.xml',
          unstableTotalAll: '0',
          unstableTotalHigh: '0',
          unstableTotalLow: '0',
          unstableTotalNormal: '0'
        ])

        step([
          $class: 'CloverPublisher',
          cloverReportDir: 'tests/_output/coverage',
          cloverReportFileName: 'coverage.xml'
        ])

        junit([
          testResults: 'tests/_output/junit/junit.xml',
          allowEmptyResults: false,
          healthScaleFactor: 1.0
        ])
      }
    }
  }
  catch (err) {
    manager.buildFailure()

    throw err
  }
}

// kate: syntax Groovy
