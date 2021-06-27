pipeline {
  agent any
  environment {
    CODING_DOCKER_REG_HOST = "${CCI_CURRENT_TEAM}-docker.pkg.${CCI_CURRENT_DOMAIN}"
    CODING_DOCKER_IMAGE_NAME = "${PROJECT_NAME.toLowerCase()}/${DOCKER_REPO_NAME}/${DOCKER_IMAGE_NAME}"
  }
  stages {
    stage("检出") {
      steps {
        checkout(
          [$class: 'GitSCM',
          branches: [[name: GIT_BUILD_REF]],
          userRemoteConfigs: [[
            url: GIT_REPO_URL,
              credentialsId: CREDENTIALS_ID
            ]]]
        )
      }
    }

    stage('安装依赖') {
      steps {
        sh "composer install"
      }
    }

    stage('单元测试') {

      steps {
        sh "./vendor/bin/phpunit --log-junit ./report.xml"
      }
      post {
        always {
          // 收集测试报告
          junit 'report.xml'
        }
      }
    }

    stage('构建镜像并推送到 CODING Docker 制品库') {
      steps {
        script {
          docker.withRegistry(
            "${CCI_CURRENT_WEB_PROTOCOL}://${CODING_DOCKER_REG_HOST}",
            "${CODING_ARTIFACTS_CREDENTIALS_ID}"
          ) {
            def dockerImage = docker.build("${CODING_DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_VERSION}", "-f ${DOCKERFILE_PATH} ${DOCKER_BUILD_CONTEXT}")
            dockerImage.push()
          }
        }
      }
    }
  }
}
