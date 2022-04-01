/* groovylint-disable */


void setBuildStatus(String message, String state) {
    step([
        $class: 'GitHubCommitStatusSetter',
        reposSource: [$class: 'ManuallyEnteredRepositorySource', url: 'https://github.com/ooobii/wumpus-copy'],
        contextSource: [$class: 'ManuallyEnteredCommitContextSource', context: 'jenkinsci/build'],
        errorHandlers: [[$class: 'ChangingBuildStatusErrorHandler', result: 'UNSTABLE']],
        statusResultSource: [ $class: 'ConditionalStatusResultSource', results: [[$class: 'AnyBuildResult', message: message, state: state]] ]
    ])
}


pipeline {
    agent { label 'linux' }
    stages {
        stage('Clone Repository') {
            steps {
                checkout([$class: 'GitSCM',
                    branches: [[name: "${GIT_BRANCH}"]],
                    extensions: [[$class: 'WipeWorkspace']],
                    userRemoteConfigs: [[url: 'git@github.com:ooobii/quick-router.git']]
                ])
            }
        }
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
                    }
                    catch (exc) {
                        echo 'WARNING: Tests Failed! Attempting to continue with package build...'
                        currentBuild.result = 'UNSTABLE'
                    }
                }
            }
        }
    }

    post {
        success {
            setBuildStatus('Build Successful', 'SUCCESS')
        }
        failure {
            setBuildStatus('Failure', 'FAILURE')
        }
        unstable {
            setBuildStatus('Unstable', 'UNSTABLE')
        }
        always {
            junit 'tests/results/junit/*.xml'
        }
    }
}
