/**
 * Killa type shim
 *
 * Redirected via tsconfig "paths": { "killa": ["./shims/killa"] }.
 * TypeScript uses this for type-checking only; webpack still bundles
 * the real killa package at runtime (paths does not affect bundling).
 *
 * Changes vs the published killa types:
 *   - Store<T>.setState  accepts fn returning Partial<T> | void
 *     (the code uses empty-block lambdas `() => {}` which return void)
 *   - Store<T>.subscribe selector accepts a function OR a string key-path
 *   - Store<T>.destroy   promoted from createStore() return to Store<T>
 */

export type State = Record<string, any>;

export type Selector<T, U = unknown> = (state: T) => U;

export interface Subscriber<T, U = unknown> {
    (state: T, prevState: T): void;
    $$subscriber?: symbol;
    $$selectorState?: U;
    $$selector?: Selector<T, U>;
}

export interface Options<T> {
    compare?: (a: unknown, b: unknown) => boolean;
    clone?: (state: T) => T;
    use?: ((store: Store<T>) => void)[];
}

export interface Store<T = State> {
    $$store: symbol;
    getState: () => T;
    setState: (fn: (state: T) => Partial<T> | void, force?: boolean) => void;
    subscribe: {
        <U>(
            subscriber: Subscriber<T, U>,
            selector?: ((state: T) => U) | string
        ): () => boolean;
    };
    getServerState: () => T;
    destroy: () => void;
}

type InitializerFn<T> = (
    getState: Store<T>['getState'],
    setState: Store<T>['setState']
) => T;

export declare function createStore<T extends State, U = InitializerFn<T> | T>(
    initializer?: U,
    options?: Options<T>
): Readonly<Store<T> & {
    resetState: (state?: unknown | null) => void;
}>;
