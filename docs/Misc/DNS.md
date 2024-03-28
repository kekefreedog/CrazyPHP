# DNS

## Flush DNS

### Mac

- Command to flush DNS :
```sh
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
```