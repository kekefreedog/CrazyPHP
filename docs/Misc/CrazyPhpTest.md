# Crazy PHP Test

To run crazy php test (bases on PHPUnit), just copy and execute the below command on the crazyphp root :

```sh
composer run-script test
```

## End-to-end tests

Create, dockerize and start real projects for every wizard choice (slow, requires Docker):

```sh
composer run-script test:e2e
```

See [NewProjectInternals.md](../NewProjectInternals.md#7-end-to-end-matrix-test) for strategies and options.
