# Message Types:

## 11: Resource Logging

msg[0]      MessageId (11)
msg[1]      SystemId
msg[2]      Reporting Hostname
msg[3]      Account Id
msg[4]      Client IP (empty on shell)
msg[5]      memory_peak_usage()
msg[6]      ru_utime.tv_usec
msg[7]      ru_stime.tv_usec