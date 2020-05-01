FROM ubuntu:20.04

ENV BEDROCK_VERSION master
EXPOSE 8888
EXPOSE 8889

HEALTHCHECK --interval=5m --timeout=3s \
  CMD (nc -z localhost 8888 && nc -z localhost 8889) || exit 1

ARG PURGEABLE_DEPENDENCIES="build-essential git"

# Ensure build environment and dependencies are available
RUN apt-get -y update && \
    apt-get -y install ${PURGEABLE_DEPENDENCIES} libpcre++-dev netcat zlib1g-dev && \
    # Clone Bedrock, checkout BEDROCK_VERSION, initialize submodules, and build
    ( \
      cd /opt && \
      git clone https://github.com/Expensify/Bedrock.git bedrock && \
      ( \
        cd bedrock && \
        git checkout ${BEDROCK_VERSION} && \
        git submodule update --init --recursive && \
        make -j$(nproc) && \
        mv bedrock /usr/local/bin \
      ) && \
      rm -rf bedrock \
    ) && \
    # Perform cleanup for smaller images
    apt-get purge -y --auto-remove ${PURGEABLE_DEPENDENCIES} && \
    # NOTE: You must create an empty database (See: https://github.com/Expensify/Bedrock/issues/489)
    mkdir /bedrock && \
    touch /bedrock/bedrock.db

# Start Bedrock
WORKDIR /bedrock
ENTRYPOINT ["bedrock", "-serverHost", "0.0.0.0:8888", "-nodeHost", "0.0.0.0:8889", "&"]
