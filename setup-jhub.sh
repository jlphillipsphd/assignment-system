## These commands assume:
# 1. You have already installed helm
# 2. You have already added the helm chart repo for jupyterhub

##################
# Before You Start
##################

# Decide on whether you are deploying the assignment system
# using docker or k8s (some configuration options will be
# different, depending on your choice). Once you decide, just
# be sure to use the appropriate command(s) when those
# options are provided below.

####################
# Basic JHub Install
####################

# First, create a namespace for your JHub
kubectl create namespace jupyterhub

# If you want to use K8S, then install JHub as follows:
helm upgrade --install --namespace jupyterhub jupyterhub jupyterhub/jupyterhub --values jhub-values-k8s.yaml

# If you want to use docker, then install JHub as follows:
helm upgrade --install --namespace jupyterhub jupyterhub jupyterhub/jupyterhub --values jhub-values-docker.yaml

#############################
# Route to JHub using Traefik
#############################

# First, you need to get the IP of your
# traefik load balancer:
kubectl -n kube-system get svc

# Copy the EXTERNAL-IP of the traefik service
# that is listed. Here is what I see for example:
NAME             TYPE           CLUSTER-IP     EXTERNAL-IP    PORT(S)                      AGE
kube-dns         ClusterIP      10.43.0.10     <none>         53/UDP,53/TCP,9153/TCP       14m
metrics-server   ClusterIP      10.43.120.77   <none>         443/TCP                      14m
traefik          LoadBalancer   10.43.94.148   192.168.96.2   80:32234/TCP,443:31814/TCP   14m

# Modify your /etc/hosts to contain an entry for that
# IP address. I used the FQDN 'k3d.local':
192.168.96.2    k3d.local

# Verify that traefik is accessible by opening
# your browser and visiting http://k3d.local/
404 Not Found

# NOTE - if you didn't get a 404, but instead it
# timed-out or some other error, then you don't
# have k3d configured correctly or your /etc/hosts
# is incorrect, etc. Try restarting k3d and/or
# editing your /etc/hosts.

# Deploy ingress route for JHub
kubectl -n jupyterhub apply -f jhub-ingress.yaml

# Open or reload http://k3d.local/ and you should
# now be directed to your JHub login page.
# You can log in and then visit the hub control panel.

################################
# Deploy Assignment System (k8s)
################################

# See next section if you want to use docker instead
kubectl create namespace cscixxxx-assignments
kubectl -n cscixxxx-assignments apply -f course-pvc.yaml
kubectl -n cscixxxx-assignments apply -f course-deployment.yaml

# Visit the services -> cscixxxx-assigments link
# from the hub control panel. You should only be
# able to see the assignment system page when logged
# in as a user. Otherwise, it will say "Forbidden".

# Note that no assignments have been set up in the
# k8s storage PVC so you will get a warning and error:
# No assignment IDs found.
# This is normal, and requires additional configuration.

###################################
# Deploy Assignment System (docker)
###################################

docker compose build
docker compose up -d

# Visit the services -> cscixxxx-assigments link
# from the hub control panel. You should only be
# able to see the assignment system page when logged
# in as a user. Otherwise, it will say "Forbidden".

# Note that no assignments have been set up in the
# docker storage volume so you will get a warning and error:
# No assignment IDs found.
# This is normal, and requires additional configuration.

################
# Clean-up (k8s)
################

k3d cluster delete

###################
# Clean-up (docker)
###################

docker compose down
k3d cluster delete
docker volume rm jhub-assignment-system_storage
