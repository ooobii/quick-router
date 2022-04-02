/* groovylint-disable */


void setBuildStatus(String context, String message, String state) {
    step([
        $class: 'GitHubCommitStatusSetter',
        reposSource: [$class: 'ManuallyEnteredRepositorySource', url: 'https://github.com/ooobii/quick-router'],
        contextSource: [$class: 'ManuallyEnteredCommitContextSource', context: "jenkinsci/$context"],
        errorHandlers: [[$class: 'ChangingBuildStatusErrorHandler', result: 'UNSTABLE']],
        statusResultSource: [ $class: 'ConditionalStatusResultSource', results: [[$class: 'AnyBuildResult', message: message, state: state]] ]
    ])
}


pipeline {
    agent { label 'linux' }
    stages {
        stage('Install Composer Pkgs') {
            steps {
                sh 'composer install'
            }
        }
        stage('Execute Tests') {
            steps {
                script {
                    try {
                        sh 'composer test'
                        clover(cloverReportDir: 'tests/results/clover', cloverReportFileName: 'coverage.xml',
                            healthyTarget: [methodCoverage: 70, conditionalCoverage: 80, statementCoverage: 80],
                            unhealthyTarget: [methodCoverage: 50, conditionalCoverage: 50, statementCoverage: 50],
                            failingTarget: [methodCoverage: 0, conditionalCoverage: 0, statementCoverage: 0]
                        )
                        setBuildStatus('test', 'Tests Successful', 'SUCCESS')
                    }
                    catch (exc) {
                        echo 'WARNING: Tests Failed! Attempting to continue with package build...'
                        currentBuild.result = 'UNSTABLE'
                        setBuildStatus('test', 'Failure', 'FAILURE')
                    }
                }
            }
        }
    }

    post {
        success {
            setBuildStatus('build', 'Build Successful', 'SUCCESS')
        }
        failure {
            setBuildStatus('build', 'Failure', 'FAILURE')
        }
        unstable {
            setBuildStatus('build', 'Unstable', 'UNSTABLE')
        }
        always {
            junit 'tests/results/junit/*.xml'
        }
    }
}
