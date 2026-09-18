/**
 * Adia a execução até que as chamadas parem por `espera` milissegundos.
 * Usado para não disparar uma requisição a cada tecla digitada na busca.
 */
export function debounce(callback, espera = 300) {
    let temporizador;

    return (...argumentos) => {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => callback(...argumentos), espera);
    };
}
