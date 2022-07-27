# JHub Assignment-System

A simple assignment system for Apache/PHP that couples with JupyterHub/Lab

Copyright &copy; 2022 Middle Tennessee State University - see LICENSE.

## Development Deployment Tutorial

You will need to consider security and storage concerns on production deployments which are outside of the scope of this repo. You will need to have a linux system with both `k3d` and `helm` installed before you begin. Also, while JHub will be deployed using k8s/z2jh below, you can choose either docker or k8s to deploy the assignment system. Just make sure you follow the appropriate commands below based on your choice.

### Step 1 - Preliminaries: set up the k8s cluster
```
k3d cluster create
```

### Step 2 - Install JHub (z2jh)

First, create a namespace for your JHub
```
kubectl create namespace jupyterhub
```
If you want to use K8S, then install JHub as follows:
```
helm upgrade --install --namespace jupyterhub jupyterhub jupyterhub/jupyterhub --values jhub-values-k8s.yaml
```
If you want to use docker, then install JHub as follows:
```
helm upgrade --install --namespace jupyterhub jupyterhub jupyterhub/jupyterhub --values jhub-values-docker.yaml
```

### Step 3 - Route FQDN to JHub using Traefik
First, you need to get the IP of yourctraefik load balancer:
```
kubectl -n kube-system get svc
```

Copy the EXTERNAL-IP of the traefik service that is listed. Here is what I see for example:
```
NAME             TYPE           CLUSTER-IP     EXTERNAL-IP    PORT(S)                      AGE
kube-dns         ClusterIP      10.43.0.10     <none>         53/UDP,53/TCP,9153/TCP       14m
metrics-server   ClusterIP      10.43.120.77   <none>         443/TCP                      14m
traefik          LoadBalancer   10.43.94.148   192.168.96.2   80:32234/TCP,443:31814/TCP   14m
```

Modify your `/etc/hosts` file to contain an entry for that IP address. I used the FQDN `k3d.local`:
```
192.168.96.2    k3d.local
```

Verify that traefik is accessible by opening your browser and visiting http://k3d.local/
```
404 Not Found
```

#### NOTE
If you didn't get a 404, but instead it timed-out or some other error, then you don't have k3d configured correctly or your `/etc/hosts` is incorrect, etc. Try restarting k3d and/or editing your `/etc/hosts`. Once you are getting the 404, then the connection is succeeding, traefik just doesn't have anywhere to route your request just yet.


Deploy ingress route for JHub using Traefik:
```
kubectl -n jupyterhub apply -f jhub-ingress.yaml
```

Open or reload http://k3d.local/ in your browser and you should now be directed to your JHub login page. You can log in and then visit the hub control panel before proceeding.

### Step 4 - Deploy JHub Assignment System (using k8s)

See the next section if you want to deploy using docker instead.
```
kubectl create namespace cscixxxx-assignments
kubectl -n cscixxxx-assignments apply -f course-pvc.yaml
kubectl -n cscixxxx-assignments apply -f course-deployment.yaml
```

Visit the services -> cscixxxx-assigments link from the hub control panel. You should only be able to see the assignment system page when authenticated with Jhub. Otherwise, it will say "Forbidden".

Note that no assignments have been set up in the k8s storage PVC so you will get a warning and error:
```
No assignment IDs found.
```
This is normal, and requires custom configuration (TBD).

### Step 4 - Deploy JHub Assignment System (using docker)
```
docker compose build
docker compose up -d
```

Visit the services -> cscixxxx-assigments link from the hub control panel. You should only be able to see the assignment system page when authenticated with Jhub. Otherwise, it will say "Forbidden".

Note that no assignments have been set up in the docker storage volume so you will get a warning and error:
```
No assignment IDs found.
```
This is normal, and requires custom configuration (TBD).

### Step 5 - Clean-up (k8s)
```
k3d cluster delete
```

### Step 5 - Clean-up (docker)
```
docker compose down
k3d cluster delete
docker volume rm jhub-assignment-system_storage
```
